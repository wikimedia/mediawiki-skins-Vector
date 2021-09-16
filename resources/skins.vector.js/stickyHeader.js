var
	STICKY_HEADER_ID = 'vector-sticky-header',
	STICKY_HEADER_APPENDED_ID = '-sticky-header',
	STICKY_HEADER_VISIBLE_CLASS = 'vector-sticky-header-visible',
	STICKY_HEADER_USER_MENU_CONTAINER_CLASS = 'vector-sticky-header-icon-end',
	FIRST_HEADING_ID = 'firstHeading',
	USER_MENU_ID = 'p-personal',
	VECTOR_USER_LINKS_SELECTOR = '.vector-user-links',
	VECTOR_MENU_CONTENT_LIST_SELECTOR = '.vector-menu-content-list',
	SEARCH_TOGGLE_SELECTOR = '.vector-sticky-header-search-toggle';

/**
 * Copies attribute from an element to another.
 *
 * @param {Element} from
 * @param {Element} to
 * @param {string} attribute
 */
function copyAttribute( from, to, attribute ) {
	var fromAttr = from.getAttribute( attribute );
	if ( fromAttr ) {
		to.setAttribute( attribute, fromAttr );
	}
}

/**
 * Suffixes an attribute with a value that indicates it
 * relates to the sticky header to support click tracking instrumentation.
 *
 * @param {Element} node
 * @param {string} attribute
 */
function suffixStickyAttribute( node, attribute ) {
	var value = node.getAttribute( attribute );
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
 * Makes sticky header icons functional for modern Vector.
 *
 * @param {HTMLElement} header
 * @param {HTMLElement|null} history
 * @param {HTMLElement|null} talk
 */
function prepareIcons( header, history, talk ) {
	var historySticky = header.querySelector( '#ca-history-sticky-header' ),
		talkSticky = header.querySelector( '#ca-talk-sticky-header' );

	if ( !historySticky || !talkSticky ) {
		throw new Error( 'Sticky header has unexpected HTML' );
	}

	if ( history ) {
		copyAttribute( history, historySticky, 'href' );
	} else {
		// @ts-ignore
		historySticky.parentNode.removeChild( historySticky );
	}
	if ( talk ) {
		copyAttribute( talk, talkSticky, 'href' );
	} else {
		// @ts-ignore
		talkSticky.parentNode.removeChild( talkSticky );
	}
}

/**
 * Makes sticky header functional for modern Vector.
 *
 * @param {HTMLElement} header
 * @param {HTMLElement} stickyIntersection
 * @param {HTMLElement} userMenu
 * @param {Element} userMenuStickyContainer
 */
function makeStickyHeaderFunctional(
	header,
	stickyIntersection,
	userMenu,
	userMenuStickyContainer
) {
	var
		/* eslint-disable-next-line compat/compat */
		stickyObserver = new IntersectionObserver( function ( entries ) {
			if ( !entries[ 0 ].isIntersecting && entries[ 0 ].boundingClientRect.top < 0 ) {
				// Viewport has crossed the bottom edge of firstHeading so show sticky header.
				// eslint-disable-next-line mediawiki/class-doc
				header.classList.add( STICKY_HEADER_VISIBLE_CLASS );
			} else {
				// Viewport is above the bottom edge of firstHeading so hide sticky header.
				// eslint-disable-next-line mediawiki/class-doc
				header.classList.remove( STICKY_HEADER_VISIBLE_CLASS );
			}
		} ),
		// Type declaration needed because of https://github.com/Microsoft/TypeScript/issues/3734#issuecomment-118934518
		userMenuClone = /** @type {HTMLElement} */( userMenu.cloneNode( true ) ),
		userMenuStickyElementsWithIds = userMenuClone.querySelectorAll( '[ id ], [ data-event-name ]' ),
		userMenuStickyContainerInner = userMenuStickyContainer
			.querySelector( VECTOR_USER_LINKS_SELECTOR );

	// Update all ids of the cloned user menu to make them unique.
	makeNodeTrackable( userMenuClone );
	userMenuStickyElementsWithIds.forEach( makeNodeTrackable );

	// Add gadget-injected items of the fixed user menu into the sticky header user menu.
	// Only applies to gadgets running after the code above and won't apply to existing items.
	mw.hook( 'util.addPortletLink' ).add( function ( /** @type {HTMLElement} */ item ) {
		// Get the nav tag parent of the gadget-injected menu item. We verify that .closest is
		// available for use because of feature detection in init function.
		var parentNav = item.closest( 'nav' );
		// Check if a gadget is injecting an item into the user menu.
		if ( parentNav && parentNav.getAttribute( 'id' ) === 'p-personal' ) {
			var
				itemClone = /** @type {HTMLElement} */ ( item.cloneNode( true ) ),
				userMenuCloneUl = userMenuClone.querySelector( VECTOR_MENU_CONTENT_LIST_SELECTOR );
			if ( userMenuCloneUl ) {
				makeNodeTrackable( itemClone );
				userMenuCloneUl.appendChild( itemClone );
			}
		}
	} );

	// Clone the updated user menu to the sticky header.
	if ( userMenuStickyContainerInner ) {
		userMenuStickyContainerInner.appendChild( userMenuClone );
	}

	prepareIcons( header,
		document.querySelector( '#ca-history a' ),
		document.querySelector( '#ca-talk a' )
	);
	stickyObserver.observe( stickyIntersection );
}

/**
 * @param {HTMLElement} header
 */
function setupSearchIfNeeded( header ) {
	var
		searchToggle = header.querySelector( SEARCH_TOGGLE_SELECTOR );

	if ( !(
		searchToggle &&
		window.fetch &&
		document.body.classList.contains( 'skin-vector-search-vue' )
	) ) {
		return;
	}

	// Load the `skins.vector.search` module here or setup an event handler to
	// load it depending on the outcome of T289718. After it loads, initialize the
	// search toggle.
	//
	// Example:
	// mw.loader.using( 'skins.vector.search', function () {
	//   initSearchToggle( searchToggle );
	// } );
}

/**
 * Determines if sticky header should be visible for a given namespace.
 *
 * @param {number} namespaceNumber
 * @return {boolean}
 */
function isAllowedNamespace( namespaceNumber ) {
	// Corresponds to Main, User, Wikipedia, Template, Help, Category, Portal, Module.
	var allowedNamespaceNumbers = [ 0, 2, 4, 10, 12, 14, 100, 828 ];
	return allowedNamespaceNumbers.indexOf( namespaceNumber ) > -1;
}

/**
 * Determines if sticky header should be visible for a given action.
 *
 * @param {string} action
 * @return {boolean}
 */
function isAllowedAction( action ) {
	var disallowedActions = [ 'history', 'edit' ],
		hasDiffId = mw.config.get( 'wgDiffOldId' );
	return disallowedActions.indexOf( action ) < 0 && !hasDiffId;
}

module.exports = function initStickyHeader() {
	var header = document.getElementById( STICKY_HEADER_ID ),
		stickyIntersection = document.getElementById(
			FIRST_HEADING_ID
		),
		userMenu = document.getElementById( USER_MENU_ID ),
		userMenuStickyContainer = document.getElementsByClassName(
			STICKY_HEADER_USER_MENU_CONTAINER_CLASS
		)[ 0 ],
		allowedNamespace = isAllowedNamespace( mw.config.get( 'wgNamespaceNumber' ) ),
		allowedAction = isAllowedAction( mw.config.get( 'wgAction' ) );

	if ( !(
		header &&
		header.closest &&
		stickyIntersection &&
		userMenu &&
		userMenuStickyContainer &&
		allowedNamespace &&
		allowedAction &&
		'IntersectionObserver' in window ) ) {
		return;
	}

	makeStickyHeaderFunctional( header, stickyIntersection, userMenu, userMenuStickyContainer );
	setupSearchIfNeeded( header );
};
