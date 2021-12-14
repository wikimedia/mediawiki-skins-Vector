/** @interface CheckboxHack */

var
	checkboxHack = /** @type {CheckboxHack} */ require( /** @type {string} */( 'mediawiki.page.ready' ) ).checkboxHack,
	CHECKBOX_HACK_CONTAINER_SELECTOR = '.vector-menu-dropdown',
	CHECKBOX_HACK_CHECKBOX_SELECTOR = '.vector-menu-checkbox',
	// In core's checkboxHack.js, it is recommended to use a  label element as a
	// button that toggles the checkbox. In Vector's dropdown menus that use the
	// Menu.mustache template, a checkbox is used as both the "button" and the
	// "checkbox".
	CHECKBOX_HACK_BUTTON_SELECTOR = '.vector-menu-checkbox',
	CHECKBOX_HACK_TARGET_SELECTOR = '.vector-menu-content';

/**
 * Add the ability for users to toggle dropdown menus using the enter key (as
 * well as space) using core's checkboxHack.
 */
function bindToggleOnSpaceEnter() {
	// Search for all dropdown containers using the CHECKBOX_HACK_CONTAINER_SELECTOR.
	var containers = document.querySelectorAll( CHECKBOX_HACK_CONTAINER_SELECTOR );

	Array.prototype.forEach.call( containers, function ( container ) {
		var
			checkbox = container.querySelector( CHECKBOX_HACK_CHECKBOX_SELECTOR ),
			button = container.querySelector( CHECKBOX_HACK_BUTTON_SELECTOR ),
			target = container.querySelector( CHECKBOX_HACK_TARGET_SELECTOR );

		if ( !( checkbox && button && target ) ) {
			return;
		}

		checkboxHack.bindToggleOnSpaceEnter( checkbox, button );
	} );
}

/**
 * T295085: Close all dropdown menus when page is unloaded to prevent them from
 * being open when navigating back to a page.
 */
function bindCloseOnUnload() {
	addEventListener( 'beforeunload', function () {
		document.querySelectorAll( CHECKBOX_HACK_CHECKBOX_SELECTOR + ':checked' )
			.forEach( function ( checkbox ) {
				/** @type {HTMLInputElement} */ ( checkbox ).checked = false;
			} );
	} );
}

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
	var $menu = $( item ).parents( '.vector-menu' );
	var menuElement = $menu.length && $menu.get( 0 ) || null;
	// Dropdowns which have not got the noicon class are icon capable.
	var isIconCapable = menuElement && menuElement.classList.contains(
		'vector-menu-dropdown'
	) && !menuElement.classList.contains(
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
	bindToggleOnSpaceEnter();
	bindCloseOnUnload();
};
