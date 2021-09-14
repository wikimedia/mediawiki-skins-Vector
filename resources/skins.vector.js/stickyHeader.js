var
	STICKY_HEADER_ID = 'vector-sticky-header',
	STICKY_HEADER_APPENDED_ID = '-sticky-header',
	STICKY_HEADER_VISIBLE_CLASS = 'vector-sticky-header-visible',
	STICKY_HEADER_USER_MENU_CONTAINER_CLASS = 'vector-sticky-header-icon-end',
	FIRST_HEADING_ID = 'firstHeading',
	USER_MENU_ID = 'p-personal',
	VECTOR_USER_LINKS_SELECTOR = '.vector-user-links',
	VECTOR_MENU_CONTENT_LIST_SELECTOR = '.vector-menu-content-list';

/**
 * Makes sticky header functional for modern Vector.
 *
 * @param {HTMLElement} header
 * @param {HTMLElement} stickyIntersection
 * @param {HTMLElement} userMenu
 * @param {HTMLElement} userMenuStickyContainer
 */
function makeStickyHeaderFunctional(
	header,
	stickyIntersection,
	userMenu,
	userMenuStickyContainer
) {
	/* eslint-disable-next-line compat/compat */
	var
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
		userMenuClone = /** @type {HTMLElement} */ ( userMenu.cloneNode( true ) ),
		userMenuStickyElementsWithIds = userMenuClone.querySelectorAll( '[ id ], [ data-event-name ]' ),
		userMenuStickyContainerInner = /** @type {HTMLElement} */ (
			userMenuStickyContainer.querySelector( VECTOR_USER_LINKS_SELECTOR )
		);

	// Update all ids of the cloned user menu to make them unique.
	userMenuClone.id += STICKY_HEADER_APPENDED_ID;
	for ( var i = 0; i < userMenuStickyElementsWithIds.length; i++ ) {
		userMenuStickyElementsWithIds[ i ].id += STICKY_HEADER_APPENDED_ID;
		// Update data attributes that need to be unique for click tracking IDs.
		var elementCloneDataEventName = userMenuStickyElementsWithIds[ i ].getAttribute( 'data-event-name' );
		if ( elementCloneDataEventName ) {
			userMenuStickyElementsWithIds[ i ].setAttribute( 'data-event-name', elementCloneDataEventName += STICKY_HEADER_APPENDED_ID );
		}
	}

	// Add gadget-injected items of the fixed user menu into the sticky header user menu.
	// Only applies to gadgets running after the code above and won't apply to existing items.
	mw.hook( 'util.addPortletLink' ).add( function ( /** @type {HTMLElement} */ item ) {
		// Get the nav tag parent of the gadget-injected menu item. We verify that .closest is
		// available for use because of feature detection in init function.
		var parentNav = /** @type {HTMLElement} */ ( item.closest( 'nav' ) );
		// Check if a gadget is injecting an item into the user menu.
		if ( parentNav.id === 'p-personal' ) {
			var
				itemClone = /** @type {HTMLElement} */ ( item.cloneNode( true ) ),
				userMenuCloneUl = /** @type {HTMLElement} */ (
					userMenuClone.querySelector( VECTOR_MENU_CONTENT_LIST_SELECTOR )
				);
			if ( userMenuCloneUl ) {
				// Remove data-event-name attribute if it exists on the cloned item.
				itemClone.removeAttribute( 'data-event-name' );
				// Update id of the cloned user menu item and add it to the cloned user menu list.
				itemClone.id += STICKY_HEADER_APPENDED_ID;
				userMenuCloneUl.appendChild( itemClone );
			}
		}
	} );

	// Clone the updated user menu to the sticky header.
	userMenuStickyContainerInner.appendChild( userMenuClone );

	stickyObserver.observe( stickyIntersection );
}

module.exports = function initStickyHeader() {
	var header = /** @type {HTMLElement} */ ( document.getElementById( STICKY_HEADER_ID ) ),
		stickyIntersection = /** @type {HTMLElement} */ ( document.getElementById(
			FIRST_HEADING_ID
		) ),
		userMenu = /** @type {HTMLElement} */ ( document.getElementById( USER_MENU_ID ) ),
		userMenuStickyContainer = /** @type {HTMLElement} */ ( document.getElementsByClassName(
			STICKY_HEADER_USER_MENU_CONTAINER_CLASS )[ 0 ]
		);

	if ( !(
		header &&
		header.closest &&
		stickyIntersection &&
		userMenu &&
		userMenuStickyContainer &&
		'IntersectionObserver' in window ) ) {
		return;
	}

	makeStickyHeaderFunctional( header, stickyIntersection, userMenu, userMenuStickyContainer );
};
