const features = require( './features.js' );
const PINNED_HEADER_CLASS = 'vector-pinnable-header-pinned';
const UNPINNED_HEADER_CLASS = 'vector-pinnable-header-unpinned';

/**
 * @param {HTMLElement} header
 */
function bindPinnableToggleButtons( header ) {
	const name = header.dataset.name;
	if ( !name ) {
		return;
	}

	const toggleButtons = header.querySelectorAll( '.vector-pinnable-header-toggle-button' );
	const pinnableElementId = header.dataset.pinnableElementId;
	const pinnedContainerId = header.dataset.pinnedContainerId;
	const unpinnedContainerId = header.dataset.unpinnedContainerId;
	const featureName = header.dataset.featureName;

	toggleButtons.forEach( function ( button ) {
		button.addEventListener( 'click', () => {
			if ( featureName ) {
				// Leverage features.js to toggle the body classes and persist the state
				// for logged-in users. features.js expects the argument passed to
				// `toggle()` to not contain the conventional `vector-` prefix so
				// replace it with a blank string.
				features.toggle( featureName );
			} else {
				// Toggle body classes, assumes default pinned classes are initialized serverside
				document.body.classList.toggle( `${name}-pinned` );
				document.body.classList.toggle( `${name}-unpinned` );
			}

			// Toggle pinned class
			header.classList.toggle( PINNED_HEADER_CLASS );
			header.classList.toggle( UNPINNED_HEADER_CLASS );

			// Optional functionality of moving the pinnable element in the DOM
			// to different containers based on it's pinned status
			if ( pinnableElementId && pinnedContainerId && unpinnedContainerId ) {
				const newContainerId = isPinned( header ) ? pinnedContainerId : unpinnedContainerId;
				movePinnableElement( pinnableElementId, newContainerId );
			}
		} );
	} );
}

/**
 * @param {HTMLElement} header
 * @return {boolean} Returns true if the element is pinned and false otherwise.
 */
function isPinned( header ) {
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
