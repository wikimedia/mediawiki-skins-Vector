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

	toggleButtons.forEach( function ( button ) {
		button.addEventListener( 'click', () => {
			// Toggle body classes, assumes default pinned classes are initialized serverside
			document.body.classList.toggle( `${name}-pinned` );
			document.body.classList.toggle( `${name}-unpinned` );

			// Toggle pinned class
			header.classList.toggle( PINNED_HEADER_CLASS );
			header.classList.toggle( UNPINNED_HEADER_CLASS );

			// Optional functionality of moving the pinnable element in the DOM
			// to different containers based on it's pinned status
			if ( pinnableElementId && pinnedContainerId && unpinnedContainerId ) {
				const pinned = document.body.classList.contains( `${name}-pinned` );
				const newContainerId = pinned ? pinnedContainerId : unpinnedContainerId;
				movePinnableElement( pinnableElementId, newContainerId );
			}
		} );
	} );
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
	PINNED_HEADER_CLASS,
	UNPINNED_HEADER_CLASS
};
