const features = require( './features.js' );
const PINNED_HEADER_CLASS = 'vector-pinnable-header-pinned';
const UNPINNED_HEADER_CLASS = 'vector-pinnable-header-unpinned';

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
function togglePinnableElement( header ) {
	const {
		pinnableElementId,
		pinnedContainerId,
		unpinnedContainerId
	} = header.dataset;

	togglePinnableClasses( header );

	// Optional functionality of moving the pinnable element in the DOM
	// to different containers based on it's pinned status
	if ( pinnableElementId && pinnedContainerId && unpinnedContainerId ) {
		const newContainerId = isPinned( header ) ? pinnedContainerId : unpinnedContainerId;
		movePinnableElement( pinnableElementId, newContainerId );
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

	const toggleButtons = header.querySelectorAll( '.vector-pinnable-header-toggle-button' );

	toggleButtons.forEach( function ( button ) {
		button.addEventListener( 'click', togglePinnableElement.bind( null, header ) );
	} );
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
	isPinned,
	PINNED_HEADER_CLASS,
	UNPINNED_HEADER_CLASS
};
