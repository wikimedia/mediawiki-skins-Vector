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
			const pinned = document.body.classList.contains( `${name}-pinned` );

			// Toggle body classes, assumes default pinned classes are initialized serverside
			document.body.classList.toggle( `${name}-pinned` );
			document.body.classList.toggle( `${name}-unpinned` );

			// Toggle pinned class
			header.classList.toggle( PINNED_HEADER_CLASS );
			header.classList.toggle( UNPINNED_HEADER_CLASS );

			// Optional functionality of moving the pinnable element in the DOM
			// to different containers based on it's pinned status
			if ( pinnableElementId && pinnedContainerId && unpinnedContainerId ) {
				movePinnableElement(
					pinnableElementId,
					pinnedContainerId,
					unpinnedContainerId,
					!pinned
				);
			}
		} );
	} );
}

/**
 * @param {string} pinnableElementId
 * @param {string} pinnedContainerId
 * @param {string} unpinnedContainerId
 * @param {boolean} pinned
 */
function movePinnableElement( pinnableElementId, pinnedContainerId, unpinnedContainerId, pinned ) {
	const pinnableElem = document.getElementById( pinnableElementId );
	const pinnedContainer = document.getElementById( pinnedContainerId );
	const unpinnedContainer = document.getElementById( unpinnedContainerId );
	const currContainer = /** @type {HTMLElement} */ ( pinnableElem && pinnableElem.parentElement );

	if ( !pinnableElem || !pinnedContainer || !unpinnedContainer || !currContainer ) {
		return;
	}

	let newContainer;
	// Avoid moving element if unnecessary
	if ( currContainer.id === unpinnedContainerId && pinned ) {
		newContainer = document.getElementById( pinnedContainerId );
	} else if ( currContainer.id === pinnedContainerId && !pinned ) {
		newContainer = document.getElementById( unpinnedContainerId );
	}

	if ( newContainer ) {
		newContainer.insertAdjacentElement( 'beforeend', pinnableElem );
	}
}

function initPinnableHeader() {
	const pinnableHeader = /** @type {NodeListOf<HTMLElement>} */ ( document.querySelectorAll( '.vector-pinnable-header' ) );
	pinnableHeader.forEach( bindPinnableToggleButtons );
}

module.exports = {
	initPinnableHeader,
	movePinnableElement,
	PINNED_HEADER_CLASS,
	UNPINNED_HEADER_CLASS
};
