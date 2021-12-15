/**
 * Functions and variables to implement sticky header.
 */
const
	STICKY_HEADER_ID = 'vector-sticky-header',
	header = document.getElementById( STICKY_HEADER_ID ),
	initSearchToggle = require( './searchToggle.js' ),
	STICKY_HEADER_APPENDED_ID = '-sticky-header',
	STICKY_HEADER_VISIBLE_CLASS = 'vector-sticky-header-visible',
	STICKY_HEADER_USER_MENU_CONTAINER_CLASS = 'vector-sticky-header-icon-end',
	FIRST_HEADING_ID = 'firstHeading',
	USER_MENU_ID = 'p-personal',
	ULS_STICKY_CLASS = 'uls-dialog-sticky',
	ULS_HIDE_CLASS = 'uls-dialog-sticky-hide',
	VECTOR_USER_LINKS_SELECTOR = '.vector-user-links',
	SEARCH_TOGGLE_SELECTOR = '.vector-sticky-header-search-toggle',
	STICKY_HEADER_EXPERIMENT_NAME = 'vector.sticky_header';

/**
 * Copies attribute from an element to another.
 *
 * @param {Element} from
 * @param {Element} to
 * @param {string} attribute
 */
function copyAttribute( from, to, attribute ) {
	const fromAttr = from.getAttribute( attribute );
	if ( fromAttr ) {
		to.setAttribute( attribute, fromAttr );
	}
}

/**
 * Show the sticky header.
 */
function show() {
	if ( header ) {
		// eslint-disable-next-line mediawiki/class-doc
		header.classList.add( STICKY_HEADER_VISIBLE_CLASS );
	}
	// eslint-disable-next-line mediawiki/class-doc
	document.body.classList.remove( ULS_HIDE_CLASS );
}

/**
 * Hide the sticky header.
 */
function hide() {
	if ( header ) {
		// eslint-disable-next-line mediawiki/class-doc
		header.classList.remove( STICKY_HEADER_VISIBLE_CLASS );
		// eslint-disable-next-line mediawiki/class-doc
		document.body.classList.add( ULS_HIDE_CLASS );
	}
}

/**
 * Copies attribute from an element to another.
 *
 * @param {Element} from
 * @param {Element} to
 */
function copyButtonAttributes( from, to ) {
	copyAttribute( from, to, 'href' );
	copyAttribute( from, to, 'title' );
}

/**
 * Suffixes an attribute with a value that indicates it
 * relates to the sticky header to support click tracking instrumentation.
 *
 * @param {Element} node
 * @param {string} attribute
 */
function suffixStickyAttribute( node, attribute ) {
	const value = node.getAttribute( attribute );
	if ( value ) {
		node.setAttribute( attribute, value + STICKY_HEADER_APPENDED_ID );
	}
}

/**
 * Makes a node trackable by our click tracking instrumentation.
 *
 * @param {Element} node
 */
function makeNodeTrackable( node ) {
	suffixStickyAttribute( node, 'id' );
	suffixStickyAttribute( node, 'data-event-name' );
}

/**
 *
 * @param {null|HTMLElement|Node|EventTarget} node
 * @return {HTMLElement}
 */
function toHTMLElement( node ) {
	// @ts-ignore
	return node;
}

/**
 * @param {HTMLElement} node
 */
function removeNode( node ) {
	toHTMLElement( node.parentNode ).removeChild( node );
}

/**
 * @param {NodeList} nodes
 * @param {string} className
 */
function removeClassFromNodes( nodes, className ) {
	Array.prototype.forEach.call( nodes, function ( node ) {
		// eslint-disable-next-line mediawiki/class-doc
		node.classList.remove( className );
	} );
}

/**
 * Makes sticky header icons functional for modern Vector.
 *
 * @param {HTMLElement} headerElement
 * @param {HTMLElement|null} history
 * @param {HTMLElement|null} talk
 */
function prepareIcons( headerElement, history, talk ) {
	const historySticky = headerElement.querySelector( '#ca-history-sticky-header' ),
		talkSticky = headerElement.querySelector( '#ca-talk-sticky-header' );

	if ( !historySticky || !talkSticky ) {
		throw new Error( 'Sticky header has unexpected HTML' );
	}

	if ( history ) {
		copyButtonAttributes( history, historySticky );
	} else {
		// @ts-ignore
		historySticky.parentNode.removeChild( historySticky );
	}
	if ( talk ) {
		copyButtonAttributes( talk, talkSticky );
	} else {
		// @ts-ignore
		talkSticky.parentNode.removeChild( talkSticky );
	}
}

/**
 * Render sticky header edit or protected page icons for modern Vector.
 *
 * @param {HTMLElement} headerElement
 * @param {HTMLElement|null} primaryEdit
 * @param {boolean} isProtected
 * @param {HTMLElement|null} secondaryEdit
 * @param {Function} disableStickyHeader function to call to disable the sticky
 *  header.
 */
function prepareEditIcons(
	headerElement,
	primaryEdit,
	isProtected,
	secondaryEdit,
	disableStickyHeader
) {
	const
		primaryEditStickyElement = headerElement.querySelector(
			'#ca-ve-edit-sticky-header'
		),
		primaryEditSticky = primaryEditStickyElement ? toHTMLElement(
			headerElement.querySelector(
				'#ca-ve-edit-sticky-header'
			)
		) : null,
		protectedSticky = toHTMLElement(
			headerElement.querySelector(
				'#ca-viewsource-sticky-header'
			)
		),
		wikitextSticky = toHTMLElement(
			headerElement.querySelector(
				'#ca-edit-sticky-header'
			)
		);

	// If no primary edit icon is present the feature is disabled.
	if ( !primaryEditSticky ) {
		return;
	}
	if ( !primaryEdit ) {
		removeNode( protectedSticky );
		removeNode( wikitextSticky );
		removeNode( primaryEditSticky );
		return;
	} else if ( isProtected ) {
		removeNode( wikitextSticky );
		removeNode( primaryEditSticky );
		copyButtonAttributes( primaryEdit, protectedSticky );
	} else {
		removeNode( protectedSticky );
		copyButtonAttributes( primaryEdit, primaryEditSticky );

		primaryEditSticky.addEventListener( 'click', function ( ev ) {
			const target = toHTMLElement( ev.target );
			const $ve = $( primaryEdit );

			if ( target && $ve.length ) {
				const event = $.Event( 'click' );
				$ve.trigger( event );
				// The link has been progressively enhanced.
				if ( event.isDefaultPrevented() ) {
					disableStickyHeader();
					ev.preventDefault();
				}
			}
		} );
		if ( secondaryEdit ) {
			copyButtonAttributes( secondaryEdit, wikitextSticky );
			wikitextSticky.addEventListener( 'click', function ( ev ) {
				const target = toHTMLElement( ev.target );
				if ( target ) {
					const $edit = $( secondaryEdit );
					if ( $edit.length ) {
						const event = $.Event( 'click' );
						$edit.trigger( event );
						// The link has been progressively enhanced.
						if ( event.isDefaultPrevented() ) {
							disableStickyHeader();
							ev.preventDefault();
						}
					}
				}
			} );
		} else {
			removeNode( wikitextSticky );
		}
	}
}

/**
 * Check if element is in viewport.
 *
 * @param {HTMLElement} element
 * @return {boolean}
 */
function isInViewport( element ) {
	const rect = element.getBoundingClientRect();
	return (
		rect.top >= 0 &&
		rect.left >= 0 &&
		rect.bottom <= ( window.innerHeight || document.documentElement.clientHeight ) &&
		rect.right <= ( window.innerWidth || document.documentElement.clientWidth )
	);
}

/**
 * Add hooks for sticky header when Visual Editor is used.
 *
 * @param {HTMLElement} targetIntersection intersection element
 * @param {IntersectionObserver} observer
 */
function addVisualEditorHooks( targetIntersection, observer ) {
	// When Visual Editor is activated, hide the sticky header.
	mw.hook( 've.activate' ).add( () => {
		hide();
		observer.unobserve( targetIntersection );
	} );

	// When Visual Editor is deactivated (by clicking "Read" tab at top of page), show sticky header
	// by re-triggering the observer.
	mw.hook( 've.deactivationComplete' ).add( () => {
		observer.observe( targetIntersection );
	} );

	// After saving edits, re-apply the sticky header if the target is not in the viewport.
	mw.hook( 'postEdit.afterRemoval' ).add( () => {
		if ( !isInViewport( targetIntersection ) ) {
			show();
			observer.observe( targetIntersection );
		}
	} );
}

/**
 * Makes sticky header functional for modern Vector.
 *
 * @param {HTMLElement} headerElement
 * @param {HTMLElement} userMenu
 * @param {Element} userMenuStickyContainer
 * @param {IntersectionObserver} stickyObserver
 * @param {HTMLElement} stickyIntersection
 */
function makeStickyHeaderFunctional(
	headerElement,
	userMenu,
	userMenuStickyContainer,
	stickyObserver,
	stickyIntersection
) {
	const
		// Type declaration needed because of https://github.com/Microsoft/TypeScript/issues/3734#issuecomment-118934518
		userMenuClone = /** @type {HTMLElement} */( userMenu.cloneNode( true ) ),
		userMenuStickyElementsWithIds = userMenuClone.querySelectorAll( '[ id ], [ data-event-name ]' ),
		userMenuStickyContainerInner = userMenuStickyContainer
			.querySelector( VECTOR_USER_LINKS_SELECTOR );

	// Update all ids of the cloned user menu to make them unique.
	makeNodeTrackable( userMenuClone );
	userMenuStickyElementsWithIds.forEach( makeNodeTrackable );

	// Remove portlet links added by gadgets using mw.util.addPortletLink, T291426
	const gadgetLinks = userMenuClone.querySelector( 'mw-list-item-js' );
	if ( gadgetLinks ) {
		gadgetLinks.remove();
	}
	removeClassFromNodes(
		userMenuClone.querySelectorAll( '.user-links-collapsible-item' ),
		'user-links-collapsible-item'
	);

	// Prevents user menu from being focusable, T290201
	const userMenuCheckbox = userMenuClone.querySelector( 'input' );
	if ( userMenuCheckbox ) {
		userMenuCheckbox.setAttribute( 'tabindex', '-1' );
	}

	// Clone the updated user menu to the sticky header.
	if ( userMenuStickyContainerInner ) {
		userMenuStickyContainerInner.appendChild( userMenuClone );
	}

	prepareIcons( headerElement,
		document.querySelector( '#ca-history a' ),
		document.querySelector( '#ca-talk a' )
	);

	const veEdit = document.querySelector( '#ca-ve-edit a' );
	const ceEdit = document.querySelector( '#ca-edit a' );
	const protectedEdit = document.querySelector( '#ca-viewsource a' );
	const isProtected = !!protectedEdit;
	const primaryEdit = protectedEdit || ( veEdit || ceEdit );
	const secondaryEdit = veEdit ? ceEdit : null;
	const disableStickyHeader = () => {
		// eslint-disable-next-line mediawiki/class-doc
		headerElement.classList.remove( STICKY_HEADER_VISIBLE_CLASS );
		stickyObserver.unobserve( stickyIntersection );
	};

	prepareEditIcons(
		headerElement,
		toHTMLElement( primaryEdit ),
		isProtected,
		toHTMLElement( secondaryEdit ),
		disableStickyHeader
	);

	stickyObserver.observe( stickyIntersection );
}

/**
 * @param {HTMLElement} headerElement
 */
function setupSearchIfNeeded( headerElement ) {
	const
		searchToggle = headerElement.querySelector( SEARCH_TOGGLE_SELECTOR );

	if ( !document.body.classList.contains( 'skin-vector-search-vue' ) ) {
		return;
	}

	if ( searchToggle ) {
		initSearchToggle( searchToggle );
	}
}

/**
 * Determines if sticky header should be visible for a given namespace.
 *
 * @param {number} namespaceNumber
 * @return {boolean}
 */
function isAllowedNamespace( namespaceNumber ) {
	// Corresponds to Main, User, Wikipedia, Template, Help, Category, Portal, Module.
	const allowedNamespaceNumbers = [ 0, 2, 4, 10, 12, 14, 100, 828 ];
	return allowedNamespaceNumbers.indexOf( namespaceNumber ) > -1;
}

/**
 * Determines if sticky header should be visible for a given action.
 *
 * @param {string} action
 * @return {boolean}
 */
function isAllowedAction( action ) {
	const disallowedActions = [ 'history', 'edit' ],
		hasDiffId = mw.config.get( 'wgDiffOldId' );
	return disallowedActions.indexOf( action ) < 0 && !hasDiffId;
}

const
	stickyIntersection = document.getElementById(
		FIRST_HEADING_ID
	),
	userMenu = document.getElementById( USER_MENU_ID ),
	userMenuStickyContainer = document.getElementsByClassName(
		STICKY_HEADER_USER_MENU_CONTAINER_CLASS
	)[ 0 ],
	allowedNamespace = isAllowedNamespace( mw.config.get( 'wgNamespaceNumber' ) ),
	allowedAction = isAllowedAction( mw.config.get( 'wgAction' ) );

/**
 * Check if all conditions are met to enable sticky header
 *
 * @return {boolean}
 */
function isStickyHeaderAllowed() {
	// @ts-ignore
	return header &&
		stickyIntersection &&
		userMenu &&
		userMenuStickyContainer &&
		allowedNamespace &&
		allowedAction &&
		'IntersectionObserver' in window;
}

/**
 * @param {IntersectionObserver} observer
 */
function initStickyHeader( observer ) {
	if ( !isStickyHeaderAllowed() || !header || !userMenu || !stickyIntersection ) {
		return;
	}

	makeStickyHeaderFunctional(
		header,
		userMenu,
		userMenuStickyContainer,
		observer,
		stickyIntersection
	);
	setupSearchIfNeeded( header );
	addVisualEditorHooks( stickyIntersection, observer );

	// Make sure ULS outside sticky header disables the sticky header behaviour.
	// @ts-ignore
	mw.hook( 'mw.uls.compact_language_links.open' ).add( function ( $trigger ) {
		if ( $trigger.attr( 'id' ) !== 'p-lang-btn-sticky-header' ) {
			const bodyClassList = document.body.classList;
			bodyClassList.remove( ULS_HIDE_CLASS );
			bodyClassList.remove( ULS_STICKY_CLASS );
		}
	} );

	// Make sure ULS dialog is sticky.
	// @ts-ignore
	const langBtn = header.querySelector( '#p-lang-btn-sticky-header' );
	if ( langBtn ) {
		langBtn.addEventListener( 'click', function () {
			const bodyClassList = document.body.classList;
			bodyClassList.remove( ULS_HIDE_CLASS );
			bodyClassList.add( ULS_STICKY_CLASS );
		} );
	}
}

module.exports = {
	show,
	hide,
	initStickyHeader,
	isStickyHeaderAllowed,
	header,
	stickyIntersection,
	STICKY_HEADER_EXPERIMENT_NAME
};
