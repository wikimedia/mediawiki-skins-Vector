/**
 * Make sure that clicking outside a menu closes it.
 */
function closeDropdownsOnClickOutside() {
	$( document.body ).on( 'click', function ( ev ) {
		var $closestPortlet = $( ev.target ).closest( '.mw-portlet' );
		// Uncheck (close) any menus that are open.
		// eslint-disable-next-line no-jquery/no-global-selector
		$( '.vector-menu-checkbox:checked' ).not(
			$closestPortlet.find( '.vector-menu-checkbox' )
		).prop( 'checked', false );
	} );
}

/**
 * @param {HTMLElement} item
 * @return {HTMLElement|null}
 */
function getVectorMenu( item ) {
	if ( item.classList.contains( 'vector-menu' ) ) {
		return item;
	} else {
		var parent = /** @type {HTMLElement} */( item.parentNode );
		return parent ? getVectorMenu( parent ) : null;
	}
}
/**
 * Adds icon placeholder for gadgets to use.
 *
 * @typedef {Object} PortletLinkData
 * @property {string|null} id
 */
/**
 * @param {HTMLElement} item
 * @param {PortletLinkData} data
 */
function addPortletLinkHandler( item, data ) {
	var link = item.querySelector( 'a' );
	var menu = getVectorMenu( item );
	// Dropdowns which have not got the noicon class are icon capable.
	var isIconCapable = menu && menu.classList.contains(
		'vector-menu-dropdown'
	) && !menu.classList.contains(
		'vector-menu-dropdown-noicon'
	);

	if ( isIconCapable && data.id && link ) {
		// If class was previously added this will be a no-op so it is safe to call even
		// if we've previously enhanced it.
		// eslint-disable-next-line mediawiki/class-doc
		link.classList.add(
			'mw-ui-icon',
			'mw-ui-icon-before',
			// The following class allows gadgets developers to style or hide an icon.
			// * mw-ui-icon-vector-gadget-<id>
			// The class is considered stable and should not be removed without
			// a #user-notice.
			'mw-ui-icon-vector-gadget-' + data.id
		);
	}
}

// Enhance previously added items.
Array.prototype.forEach.call(
	document.querySelectorAll( '.mw-list-item-js' ),
	function ( item ) {
		addPortletLinkHandler( item, {
			id: item.getAttribute( 'id' )
		} );
	}
);

mw.hook( 'util.addPortletLink' ).add( addPortletLinkHandler );

module.exports = function dropdownMenus() {
	closeDropdownsOnClickOutside();
};
