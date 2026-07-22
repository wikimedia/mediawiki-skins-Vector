const languageButton = require( './languageButton.js' ),
	// Note: the entire module is exported later for use inside WikimediaEvents
	pinnableElement = require( './pinnableElement.js' ),
	searchToggle = require( './searchToggle.js' ),
	portletsManager = require( './portlets.js' ),
	dropdownMenus = require( './dropdownMenus.js' ).dropdownMenus,
	tables = require( './tables.js' ).init,
	watchstar = require( './watchstar.js' ).init,
	setupIntersectionObservers = require( './setupIntersectionObservers.js' ),
	menuTabs = require( './menuTabs.js' ),
	pageToolbar = require( './pageToolbar.js' ),
	userPreferences = require( './userPreferences.js' ),
	{ isNightModeGadgetEnabled, disableNightModeForGadget, alterExclusionMessage, removeBetaNotice } = require( './disableNightModeIfGadget.js' ),
	teleportTarget = /** @type {HTMLElement} */require( /** @type {string} */ ( 'mediawiki.page.ready' ) ).teleportTarget;

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
 *     transform: translateX( -100% );
 * }
 *
 * // This transition will be disabled initially for JavaScript users. It will never be enabled for
 * // non-JavaScript users.
 * .vector-animations-ready .foo {
 *     transition: transform 100ms ease-out;
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
 * @param {Window} window
 * @return {void}
 */
function main( window ) {
	enableCssAnimations( window.document );
	languageButton();
	portletsManager.main();
	watchstar();
	// Initialize the search toggle for the main header only. The sticky header
	// toggle is initialized after Codex search loads.
	/** @type {HTMLElement|null} */
	const searchToggleElement = document.querySelector( '.mw-header .search-toggle' );
	if ( searchToggleElement ) {
		searchToggle( searchToggleElement );
	}
	pinnableElement.init();
	// Initializes the TOC and sticky header, behaviour of which depend on scroll behaviour.
	setupIntersectionObservers.main();
	// Apply body styles to teleported elements
	teleportTarget.classList.add( 'vector-body' );

	// Load client preferences
	const appearanceMenuSelector = '#vector-appearance';
	const appearanceMenuExists = document.querySelectorAll( appearanceMenuSelector ).length > 0;
	if ( appearanceMenuExists ) {
		mw.loader.using( [
			'skins.vector.clientPreferences',
			'skins.vector.search.codex.styles'
		] ).then( () => {
			const clientPreferences = require( /** @type {string} */ ( 'skins.vector.clientPreferences' ) );
			const clientPreferenceConfig = ( require( './clientPreferences.json' ) );

			// while we're in beta, temporarily check if the night mode gadget is installed and
			// disable our night mode if so
			if ( isNightModeGadgetEnabled() ) {
				disableNightModeForGadget();
				clientPreferences.render(
					appearanceMenuSelector, clientPreferenceConfig, userPreferences
				);
				alterExclusionMessage();
				removeBetaNotice();
			} else {
				clientPreferences.render(
					appearanceMenuSelector, clientPreferenceConfig, userPreferences
				);
			}
		} );
	}

	dropdownMenus();
	// menuTabs should follow `dropdownMenus` as that can move menu items from a
	// tab menu to a dropdown.
	menuTabs();
	tables();
	pageToolbar();
}

if ( document.readyState === 'interactive' || document.readyState === 'complete' ) {
	main( window );
} else {
	// This is needed when document.readyState === 'loading'.
	document.addEventListener( 'DOMContentLoaded', () => {
		main( window );
	} );
}

// Provider of skins.vector.js module:
/**
 * skins.vector.js
 *
 * @stable for use inside WikimediaEvents ONLY.
 */
module.exports = { pinnableElement };
