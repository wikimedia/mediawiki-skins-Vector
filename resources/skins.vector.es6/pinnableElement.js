const features = require( './features.js' );
const PINNED_HEADER_CLASS = 'vector-pinnable-header-pinned';
const UNPINNED_HEADER_CLASS = 'vector-pinnable-header-unpinned';

/**
 * Callback for matchMedia listener that overrides the pinnable header's stored state
 * at a certain breakpoint and forces it to unpin. Also hides the pinnable button
 * at that breakpoint to disable pinning.
 * Usage of 'e.matches' assumes a `max-width` not `min-width` media query.
 *
 * @param {HTMLElement} header
 * @param {MediaQueryList|MediaQueryListEvent} e
 */
function disablePinningAtBreakpoint( header, e ) {
	const {
		pinnableElementId,
		pinnedContainerId,
		unpinnedContainerId,
		featureName
	} = header.dataset;
	const savedPinnedState = JSON.parse( header.dataset.savedPinnedState || 'false' );

	// (typescript null check)
	if ( !( pinnableElementId && unpinnedContainerId && pinnedContainerId && featureName ) ) {
		return;
	}

	// Hide the button at lower resolutions.
	header.hidden = e.matches;

	// FIXME: Class toggling should be centralized instead of being
	// handled here, in features.js and togglePinnableClasses().
	if ( e.matches && savedPinnedState === true ) {
		features.toggleDocClasses( featureName, false );
		header.classList.remove( PINNED_HEADER_CLASS );
		header.classList.add( UNPINNED_HEADER_CLASS );
		movePinnableElement( pinnableElementId, unpinnedContainerId );
	}

	if ( !e.matches && savedPinnedState === true ) {
		features.toggleDocClasses( featureName, true );
		header.classList.add( PINNED_HEADER_CLASS );
		header.classList.remove( UNPINNED_HEADER_CLASS );
		movePinnableElement( pinnableElementId, pinnedContainerId );
	}
}

/**
 * Saves the persistent pinnable state in the element's dataset
 * so that it can be overridden at lower resolutions and the
 * reverted to at wider resolutions.
 *
 * This is not necessarily the elements current state, but it
 * seeks to represent the state of the saved user preference.
 *
 * @param {HTMLElement} header
 */
function setSavedPinnableState( header ) {
	header.dataset.savedPinnedState = String( isPinned( header ) );
}

/**
 * Toggle classes on the body and pinnable element
 *
 * @param {HTMLElement} header pinnable element
 */
function togglePinnableClasses( header ) {
	const { featureName, name } = header.dataset;

	if ( featureName ) {
		// Leverage features.js to toggle the body classes and persist the state
		// for logged-in users.
		features.toggle( featureName );
	} else {
		// Toggle body classes, assumes default pinned classes are initialized serverside
		document.body.classList.toggle( `${name}-pinned` );
		document.body.classList.toggle( `${name}-unpinned` );
	}

	// Toggle pinned class
	header.classList.toggle( PINNED_HEADER_CLASS );
	header.classList.toggle( UNPINNED_HEADER_CLASS );
}

/**
 * Event handler that toggles the pinnable elements pinned state.
 * Also moves the pinned element when those params are provided
 * (via data attributes).
 *
 * @param {HTMLElement} header PinnableHeader element.
 */
function pinnableElementClickHandler( header ) {
	const {
		pinnableElementId,
		pinnedContainerId,
		unpinnedContainerId
	} = header.dataset;

	togglePinnableClasses( header );
	setSavedPinnableState( header );

	// Optional functionality of moving the pinnable element in the DOM
	// to different containers based on it's pinned status
	if ( pinnableElementId && pinnedContainerId && unpinnedContainerId ) {
		const newContainerId = isPinned( header ) ? pinnedContainerId : unpinnedContainerId;
		movePinnableElement( pinnableElementId, newContainerId );
		setFocusAfterToggle( pinnableElementId );
	}
}

/**
 * Sets focus on the correct toggle button depending on the pinned state.
 * Also opens the dropdown containing the unpinned element.
 *
 * @param {string} pinnableElementId
 */
function setFocusAfterToggle( pinnableElementId ) {
	let focusElement;
	const pinnableElement = document.getElementById( pinnableElementId );
	const header = /** @type {HTMLElement|null} */ ( pinnableElement && pinnableElement.querySelector( '.vector-pinnable-header' ) );
	if ( !pinnableElement || !header ) {
		return;
	}
	if ( isPinned( header ) ) {
		focusElement = /** @type {HTMLElement|null} */ ( pinnableElement.querySelector( '.vector-pinnable-header-unpin-button' ) );
	} else {
		const dropdown = pinnableElement.closest( '.vector-dropdown' );
		focusElement = /** @type {HTMLInputElement|null} */ ( dropdown && dropdown.querySelector( '.vector-menu-checkbox' ) );
	}
	if ( focusElement ) {
		focusElement.focus();
	}
}

/**
 * Binds all the toggle buttons in a pinnableElement
 * to the click handler that enables pinnability.
 *
 * @param {HTMLElement} header
 */
function bindPinnableToggleButtons( header ) {
	if ( !header.dataset.name ) {
		return;
	}

	const pinnableBreakpoint = window.matchMedia( '(max-width: 1000px)' );
	const toggleButtons = header.querySelectorAll( '.vector-pinnable-header-toggle-button' );

	toggleButtons.forEach( function ( button ) {
		button.addEventListener( 'click', pinnableElementClickHandler.bind( null, header ) );
	} );
	// set saved pinned state for narrow breakpoint behaviour.
	setSavedPinnableState( header );
	// Check the breakpoint in case an override is needed on pageload.
	disablePinningAtBreakpoint( header, pinnableBreakpoint );
	// Add match media handler.
	if ( pinnableBreakpoint.addEventListener ) {
		pinnableBreakpoint.addEventListener( 'change', disablePinningAtBreakpoint.bind( null, header ) );
	} else {
		// Before Safari 14, MediaQueryList is based on EventTarget,
		// so you must use addListener() and removeListener() to observe media query lists.
		pinnableBreakpoint.addListener( disablePinningAtBreakpoint.bind( null, header ) );
	}
}

/**
 * @param {HTMLElement} header
 * @return {boolean} Returns true if the element is pinned and false otherwise.
 */
function isPinned( header ) {
	// In the future, consider delegating to `features.isEnabled()` if and when
	// TOC (T316060, T325032) and all pinnable elements use the
	// `vector-feature-{name}-pinned-enabled` naming convention for the body
	// class.
	return header.classList.contains( PINNED_HEADER_CLASS );
}

/**
 * @param {string} pinnableElementId
 * @param {string} newContainerId
 */
function movePinnableElement( pinnableElementId, newContainerId ) {
	const pinnableElem = document.getElementById( pinnableElementId );
	const newContainer = document.getElementById( newContainerId );
	const currContainer = /** @type {HTMLElement} */ ( pinnableElem && pinnableElem.parentElement );

	if ( !pinnableElem || !newContainer || !currContainer ) {
		return;
	}

	// Avoid moving element if unnecessary
	if ( currContainer.id !== newContainerId ) {
		newContainer.insertAdjacentElement( 'beforeend', pinnableElem );
	}
}

function initPinnableElement() {
	const pinnableHeader = /** @type {NodeListOf<HTMLElement>} */ ( document.querySelectorAll( '.vector-pinnable-header' ) );
	pinnableHeader.forEach( bindPinnableToggleButtons );
}

module.exports = {
	initPinnableElement,
	movePinnableElement,
	setFocusAfterToggle,
	isPinned,
	PINNED_HEADER_CLASS,
	UNPINNED_HEADER_CLASS
};
