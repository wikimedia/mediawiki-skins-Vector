// Enable Vector features limited to ES6 browse
const
	searchToggle = require( './searchToggle.js' ),
	stickyHeader = require( './stickyHeader.js' ),
	scrollObserver = require( './scrollObserver.js' ),
	AB = require( './AB.js' ),
	initSectionObserver = require( './sectionObserver.js' ),
	initTableOfContents = require( './tableOfContents.js' ),
	TOC_ID = 'mw-panel-toc',
	BODY_CONTENT_ID = 'bodyContent',
	HEADLINE_SELECTOR = '.mw-headline',
	TOC_SECTION_ID_PREFIX = 'toc-';

/**
 * @return {void}
 */
const main = () => {
	// Initialize the search toggle for the main header only. The sticky header
	// toggle is initialized after wvui search loads.
	const searchToggleElement = document.querySelector( '.mw-header .search-toggle' );
	if ( searchToggleElement ) {
		searchToggle( searchToggleElement );
	}

	// Get the A/B test config for sticky header if enabled.
	const
		FEATURE_TEST_GROUP = 'stickyHeaderEnabled',
		testConfig = AB.getEnabledExperiment(),
		stickyConfig = testConfig &&
			// @ts-ignore
			testConfig.experimentName === stickyHeader.STICKY_HEADER_EXPERIMENT_NAME ?
			testConfig : null,
		// Note that the default test group is set to experience the feature by default.
		// @ts-ignore
		testGroup = stickyConfig ? stickyConfig.group : FEATURE_TEST_GROUP,
		targetElement = stickyHeader.header,
		targetIntersection = stickyHeader.stickyIntersection,
		isStickyHeaderAllowed = stickyHeader.isStickyHeaderAllowed() &&
			testGroup !== 'unsampled' && AB.isInTestGroup( testGroup, FEATURE_TEST_GROUP );

	// Fire the A/B test enrollment hook.
	AB.initAB( testGroup );

	// Set up intersection observer for sticky header functionality and firing scroll event hooks
	// for event logging if AB test is enabled.
	const observer = scrollObserver.initScrollObserver(
		() => {
			if ( targetElement && isStickyHeaderAllowed ) {
				stickyHeader.show();
			}
			scrollObserver.fireScrollHook( 'down' );
		},
		() => {
			if ( targetElement && isStickyHeaderAllowed ) {
				stickyHeader.hide();
			}
			scrollObserver.fireScrollHook( 'up' );
		}

	);

	if ( isStickyHeaderAllowed ) {
		stickyHeader.initStickyHeader( observer );
	} else if ( targetIntersection ) {
		observer.observe( targetIntersection );
	}

	// Table of contents
	const tocElement = document.getElementById( TOC_ID );
	const bodyContent = document.getElementById( BODY_CONTENT_ID );

	if ( !(
		tocElement &&
		bodyContent &&
		window.IntersectionObserver &&
		window.requestAnimationFrame )
	) {
		return;
	}

	// eslint-disable-next-line prefer-const
	let /** @type {initSectionObserver.SectionObserver} */ sectionObserver;
	const tableOfContents = initTableOfContents( {
		container: tocElement,
		onSectionClick: () => {
			sectionObserver.pause();

			// Ensure the browser has finished painting and has had enough time to
			// scroll to the section before resuming section observer. One rAF should
			// be sufficient in most browsers, but Firefox 96.0.2 seems to require two
			// rAFs.
			requestAnimationFrame( () => {
				requestAnimationFrame( () => {
					sectionObserver.resume();
				} );
			} );
		}
	} );
	sectionObserver = initSectionObserver( {
		container: bodyContent,
		tagNames: [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ],
		topMargin: targetElement ? targetElement.getBoundingClientRect().height : 0,
		/**
		 * @param {HTMLElement} section
		 */
		onIntersection: ( section ) => {
			const headline = section.querySelector( HEADLINE_SELECTOR );

			if ( headline ) {
				tableOfContents.activateSection( TOC_SECTION_ID_PREFIX + headline.id );
			}
		}
	} );
};

module.exports = {
	main: main
};
