/** @type {CheckboxHack} */ var checkboxHack =
	require( /** @type {string} */ ( 'mediawiki.page.ready' ) ).checkboxHack;
var collapsibleTabs = require( '../skins.vector.legacy.js/collapsibleTabs.js' );
var vector = require( '../skins.vector.legacy.js/vector.js' );

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

		checkboxHack.updateAriaExpanded( checkbox );
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
