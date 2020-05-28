/** @type {CheckboxHack} */ var checkboxHack =
	require( /** @type {string} */ ( 'mediawiki.page.ready' ) ).checkboxHack;
var collapsibleTabs = require( '../skins.vector.legacy.js/collapsibleTabs.js' );
var vector = require( '../skins.vector.legacy.js/vector.js' );

/**
 * Wait for first paint before calling this function. That's its whole purpose.
 *
 * Some CSS animations and transitions are "disabled" by default as a workaround to this old Chrome
 * bug, https://bugs.chromium.org/p/chromium/issues/detail?id=332189, which otherwise causes them to
 * render in their terminal state on page load. By adding the `vector-animations-ready` class to the
 * `html` root element **after** first paint, the animation selectors suddenly match causing the
 * animations to become "enabled" when they will work properly. A similar pattern is used in Minerva
 * (see T234570#5779890, T246419).
 *
 * Example usage in Less:
 *
 * ```less
 * .foo {
 *     color: #f00;
 *     .transform( translateX( -100% ) );
 * }
 *
 * // This transition will be disabled initially for JavaScript users. It will never be enabled for
 * // no-JS users.
 * .vector-animations-ready .foo {
 *     .transition( transform 100ms ease-out; );
 * }
 * ```
 *
 * @param {Document} document
 * @return {void}
 */
function enableCssAnimations( document ) {
	document.documentElement.classList.add( 'vector-animations-ready' );
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

		checkboxHack.updateAriaExpanded( checkbox );
	}
}

/**
 * @param {Window} window
 * @return {void}
 */
function main( window ) {
	enableCssAnimations( window.document );
	collapsibleTabs.init();
	$( vector.init );
	initSidebar( window.document );
}

main( window );
