// Enable Vector features limited to ES6 browse
const
	limitedWidthToggle = require( './limitedWidthToggle.js' ),
	searchToggle = require( './searchToggle.js' ),
	stickyHeader = require( './stickyHeader.js' ),
	scrollObserver = require( './scrollObserver.js' ),
	initExperiment = require( './AB.js' ),
	initSectionObserver = require( './sectionObserver.js' ),
	initTableOfContents = require( './tableOfContents.js' ),
	pinnableElement = require( './pinnableElement.js' ),
	deferUntilFrame = require( './deferUntilFrame.js' ),
	ABTestConfig = require( /** @type {string} */ ( './config.json' ) ).wgVectorWebABTestEnrollment || {},
	stickyHeaderEditIconConfig = require( /** @type {string} */ ( './config.json' ) ).wgVectorStickyHeaderEdit || true,
	STICKY_HEADER_VISIBLE_CLASS = 'vector-sticky-header-visible',
	TOC_ID = 'mw-panel-toc',
	BODY_CONTENT_ID = 'bodyContent',
	HEADLINE_SELECTOR = '.mw-headline',
	TOC_SECTION_ID_PREFIX = 'toc-',
	PAGE_TITLE_SCROLL_HOOK = 'page_title',
	PAGE_TITLE_INTERSECTION_CLASS = 'vector-below-page-title';

const belowDesktopMedia = window.matchMedia( '(max-width: 999px)' );

/**
 * @callback OnIntersection
 * @param {HTMLElement} element The section that triggered the new intersection change.
 */

/**
 * @ignore
 * @param {Function} changeActiveSection
 * @return {OnIntersection}
 */
const getHeadingIntersectionHandler = ( changeActiveSection ) => {
	/**
	 * @param {HTMLElement} section
	 */
	return ( section ) => {
		const headline = section.classList.contains( 'mw-body-content' ) ?
			section :
			section.querySelector( HEADLINE_SELECTOR );
		if ( headline ) {
			changeActiveSection( `${TOC_SECTION_ID_PREFIX}${headline.id}` );
		}
	};
};

/**
 * Initialize sticky header AB tests and determine whether to show the sticky header
 * based on which buckets the user is in.
 *
 * @typedef {Object} InitStickyHeaderABTests
 * @property {boolean} disableEditIcons - Should the sticky header have an edit icon
 * @property {boolean} showStickyHeader - Should the sticky header be shown
 * @param {ABTestConfig} abConfig
 * @param {boolean} isStickyHeaderFeatureAllowed and the user is logged in
 * @param {function(ABTestConfig): initExperiment.WebABTest} getEnabledExperiment
 * @return {InitStickyHeaderABTests}
 */
function initStickyHeaderABTests( abConfig, isStickyHeaderFeatureAllowed, getEnabledExperiment ) {
	let show = isStickyHeaderFeatureAllowed,
		stickyHeaderExperiment,
		noEditIcons = stickyHeaderEditIconConfig;

	// One of the sticky header AB tests is specified in the config
	const abTestName = abConfig.name,
		isStickyHeaderExperiment = abTestName === stickyHeader.STICKY_HEADER_EXPERIMENT_NAME ||
			abTestName === stickyHeader.STICKY_HEADER_EDIT_EXPERIMENT_NAME;

	// Determine if user is eligible for sticky header AB test
	if (
		isStickyHeaderFeatureAllowed && // The sticky header can be shown on the page
		abConfig.enabled && // An AB test config is enabled
		isStickyHeaderExperiment // The AB test is one of the sticky header experiments
	) {
		// If eligible, initialize the AB test
		stickyHeaderExperiment = getEnabledExperiment( abConfig );

		// If running initial or edit AB test, show sticky header to treatment groups
		// only. Unsampled and control buckets do not see sticky header.
		if ( abTestName === stickyHeader.STICKY_HEADER_EXPERIMENT_NAME ||
			abTestName === stickyHeader.STICKY_HEADER_EDIT_EXPERIMENT_NAME
		) {
			show = stickyHeaderExperiment.isInTreatmentBucket();
		}

		// If running edit-button AB test, the edit buttons in sticky header are shown
		// to second treatment group only.
		if ( abTestName === stickyHeader.STICKY_HEADER_EDIT_EXPERIMENT_NAME ) {
			if ( stickyHeaderExperiment.isInTreatmentBucket( '1' ) ) {
				noEditIcons = true;
			}
			if ( stickyHeaderExperiment.isInTreatmentBucket( '2' ) ) {
				noEditIcons = false;
			}
		}
	} else if (
		// T310750 Account for when the current experiment is not sticky header or it's disabled.
		isStickyHeaderFeatureAllowed && ( !abConfig.enabled || !isStickyHeaderExperiment )
	) {
		noEditIcons = false;
	}

	return {
		showStickyHeader: show,
		disableEditIcons: noEditIcons
	};
}

/*
 * Updates TOC's location in the DOM (in sidebar or sticky header)
 * depending on if the TOC is collapsed and if the sticky header is visible
 *
 * @return {void}
 */
const updateTocLocation = () => {
	const TOC_PINNED_CLASS = 'vector-toc-pinned';
	const isPinned = document.body.classList.contains( TOC_PINNED_CLASS );
	const isStickyHeaderVisible = document.body.classList.contains( STICKY_HEADER_VISIBLE_CLASS );
	const isBelowDesktop = belowDesktopMedia.matches;

	const pinnedContainerId = 'vector-toc-pinned-container';
	// FIXME: Delete the conditional after has been in prod for 5 days
	const stickyHeaderUnpinnedContainerId = document.getElementById( 'vector-sticky-header-toc-content-container' ) ?
		'vector-sticky-header-toc-content-container' :
		'vector-sticky-header-toc-unpinned-container';
	const pageTitlebarUnpinnedContainerId = 'vector-page-titlebar-toc-unpinned-container';

	let newContainerId = '';
	if ( isPinned ) {
		if ( isBelowDesktop ) {
			// Automatically move the ToC into the page titlebar when pinned on smaller resolutions
			newContainerId = pageTitlebarUnpinnedContainerId;
		} else {
			newContainerId = pinnedContainerId;
		}
	} else {
		if ( isStickyHeaderVisible && !isBelowDesktop ) {
			newContainerId = stickyHeaderUnpinnedContainerId;
		} else {
			newContainerId = pageTitlebarUnpinnedContainerId;
		}
	}

	pinnableElement.movePinnableElement( TOC_ID, newContainerId );
};

/**
 * @return {void}
 */
const main = () => {
	limitedWidthToggle();
	// Initialize the search toggle for the main header only. The sticky header
	// toggle is initialized after Codex search loads.
	const searchToggleElement = document.querySelector( '.mw-header .search-toggle' );
	if ( searchToggleElement ) {
		searchToggle( searchToggleElement );
	}

	//
	// Pinnable elements
	//
	pinnableElement.initPinnableElement();

	//
	// Sticky header
	//
	const
		header = document.getElementById( stickyHeader.STICKY_HEADER_ID ),
		stickyIntersection = document.getElementById( stickyHeader.FIRST_HEADING_ID ),
		userMenu = document.getElementById( stickyHeader.USER_MENU_ID ),
		allowedNamespace = stickyHeader.isAllowedNamespace( mw.config.get( 'wgNamespaceNumber' ) ),
		allowedAction = stickyHeader.isAllowedAction( mw.config.get( 'wgAction' ) );

	const isStickyHeaderAllowed =
		!!header &&
		!!stickyIntersection &&
		!!userMenu &&
		allowedNamespace &&
		allowedAction &&
		'IntersectionObserver' in window;

	const { showStickyHeader, disableEditIcons } = initStickyHeaderABTests(
		ABTestConfig,
		isStickyHeaderAllowed && !mw.user.isAnon(),
		( config ) => initExperiment(
			Object.assign( {}, config, { token: mw.user.getId() } )
		)
	);

	// Set up intersection observer for page title
	// Used to show/hide sticky header and add class used by collapsible TOC (T307900)
	const observer = scrollObserver.initScrollObserver(
		() => {
			if ( isStickyHeaderAllowed && showStickyHeader ) {
				stickyHeader.show();
				updateTocLocation();
			}
			document.body.classList.add( PAGE_TITLE_INTERSECTION_CLASS );
			scrollObserver.fireScrollHook( 'down', PAGE_TITLE_SCROLL_HOOK );
		},
		() => {
			if ( isStickyHeaderAllowed && showStickyHeader ) {
				stickyHeader.hide();
				updateTocLocation();
			}
			document.body.classList.remove( PAGE_TITLE_INTERSECTION_CLASS );
			scrollObserver.fireScrollHook( 'up', PAGE_TITLE_SCROLL_HOOK );
		}
	);

	// Handle toc location when sticky header is hidden on lower viewports
	belowDesktopMedia.onchange = () => {
		updateTocLocation();
	};

	updateTocLocation();

	if ( !showStickyHeader ) {
		stickyHeader.hide();
	}

	if ( isStickyHeaderAllowed && showStickyHeader ) {
		stickyHeader.initStickyHeader( {
			header,
			userMenu,
			observer,
			stickyIntersection,
			disableEditIcons
		} );
	} else if ( stickyIntersection ) {
		observer.observe( stickyIntersection );
	}

	//
	//  Table of contents
	//
	const tocElement = document.getElementById( TOC_ID );
	const bodyContent = document.getElementById( BODY_CONTENT_ID );

	if ( !(
		tocElement &&
		bodyContent &&
		window.IntersectionObserver &&
		window.requestAnimationFrame
	) ) {
		return;
	}

	const tableOfContents = initTableOfContents( {
		container: tocElement,
		onHeadingClick: ( id ) => {

			// eslint-disable-next-line no-use-before-define
			sectionObserver.pause();

			tableOfContents.expandSection( id );
			tableOfContents.changeActiveSection( id );

			// T297614: We want the link that the user has clicked inside the TOC to
			// be "active" (e.g. bolded) regardless of whether the browser's scroll
			// position corresponds to that section. Therefore, we need to temporarily
			// ignore section observer until the browser has finished scrolling to the
			// section (if needed).
			//
			// However, because the scroll event happens asyncronously after the user
			// clicks on a link and may not even happen at all (e.g. the user has
			// scrolled all the way to the bottom and clicks a section that is already
			// in the viewport), determining when we should resume section observer is
			// a bit tricky.
			//
			// Because a scroll event may not even be triggered after clicking the
			// link, we instead allow the browser to perform a maximum number of
			// repaints before resuming sectionObserver. Per T297614#7687656, Firefox
			// 97.0 wasn't consistently activating the table of contents section that
			// the user clicked even after waiting 2 frames. After further
			// investigation, it sometimes waits up to 3 frames before painting the
			// new scroll position so we have that as the limit.
			//
			// eslint-disable-next-line no-use-before-define
			deferUntilFrame( () => sectionObserver.resume(), 3 );
		},
		onToggleClick: ( id ) => {
			tableOfContents.toggleExpandSection( id );
		},
		onTogglePinned: updateTocLocation
	} );
	const headingSelector = [
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
	].map( ( tag ) => `.mw-parser-output ${tag}` ).join( ',' );
	const sectionObserver = initSectionObserver( {
		elements: bodyContent.querySelectorAll( `${headingSelector}, .mw-body-content` ),
		topMargin: header ? header.getBoundingClientRect().height : 0,
		onIntersection: getHeadingIntersectionHandler( tableOfContents.changeActiveSection )
	} );
};

module.exports = {
	main,
	test: {
		initStickyHeaderABTests,
		getHeadingIntersectionHandler
	}
};
