/** @type {CheckboxHack} */ var checkboxHack =
	require( /** @type {string} */ ( 'mediawiki.page.ready' ) ).checkboxHack;
var collapsibleTabs = require( '../skins.vector.legacy.js/collapsibleTabs.js' );
var vector = require( '../skins.vector.legacy.js/vector.js' );

/**
 * Update the state of the menu icon to be an expanded or collapsed icon.
 * @param {HTMLInputElement} checkbox
 * @param {HTMLElement} button
 * @return {void}
 */
function updateMenuIcon( checkbox, button ) {
	button.classList.remove(
		checkbox.checked ?
			'mw-ui-icon-wikimedia-menu-base20' :
			'mw-ui-icon-wikimedia-collapseHorizontal-base20'
	);
	button.classList.add(
		checkbox.checked ?
			'mw-ui-icon-wikimedia-collapseHorizontal-base20' :
			'mw-ui-icon-wikimedia-menu-base20'
	);
}

/**
 * Improve the interactivity of the sidebar panel by binding optional checkbox hack enhancements
 * for focus and `aria-expanded`. Also, flip the icon image on click.
 * @param {Document} document
 * @return {void}
 */
function initSidebar( document ) {
	var checkbox = document.getElementById( 'mw-sidebar-checkbox' );
	var button = document.getElementById( 'mw-sidebar-button' );
	if ( checkbox instanceof HTMLInputElement && button ) {
		checkboxHack.bindToggleOnClick( checkbox, button );
		checkboxHack.bindUpdateAriaExpandedOnInput( checkbox );

		button.addEventListener( 'click', updateMenuIcon.bind( undefined, checkbox, button ) );
		checkbox.addEventListener( 'input', updateMenuIcon.bind( undefined, checkbox, button ) );

		checkboxHack.updateAriaExpanded( checkbox );
		updateMenuIcon( checkbox, button );
	}
}

/**
 * @param {Window} window
 * @return {void}
 */
function main( window ) {
	collapsibleTabs.init();
	$( vector.init );
	initSidebar( window.document );
}

main( window );
