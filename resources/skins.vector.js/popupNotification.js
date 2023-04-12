// Store active notifications to only show one at a time, for use inside clearHints and showHint
const /** @type {Record<string,OoUiPopupWidget>} */ activeNotification = {};

/**
 * Adds and show a popup to the user to point them to the new location of the element
 *
 * @param {HTMLElement} container
 * @param {string} message
 * @param {string} id
 * @param {string[]} [classes]
 * @param {number|false} [timeout]
 * @param {Function} [onDismiss]
 * @return {JQuery.Promise<OoUiPopupWidget|undefined>}
 */
function add( container, message, id, classes = [], timeout = 4000, onDismiss = () => {} ) {
	/**
	 * @type {OoUiPopupWidget}
	 */
	let popupWidget;
	// clear existing hints.
	if ( id && activeNotification[ id ] ) {
		remove( activeNotification[ id ] );
		delete activeNotification[ id ];
	}
	// load oojs-ui if it's not already loaded
	// FIXME: This should be replaced with Codex.
	return mw.loader.using( 'oojs-ui-core' ).then( () => {
		const content = document.createElement( 'p' );
		content.textContent = message;
		popupWidget = new OO.ui.PopupWidget( {
			$content: $( content ),
			padded: true,
			autoClose: timeout !== false,
			head: timeout === false,
			anchor: true,
			align: 'center',
			position: 'below',
			classes: [ 'vector-popup-notification' ].concat( classes ),
			container
		} );
		popupWidget.$element.appendTo( container );
		if ( popupWidget && id ) {
			activeNotification[ id ] = popupWidget;
		}
		popupWidget.on( 'closing', () => {
			onDismiss();
		} );
		show( popupWidget, timeout );
		return popupWidget;
	} );
}
/**
 * Toggle the popup widget
 *
 * @param {OoUiPopupWidget} popupWidget popupWidget from oojs-ui
 * cannot use type because it's not loaded yet
 */
function remove( popupWidget ) {
	popupWidget.toggle( false );
	popupWidget.$element.remove();
}
/**
 * Toggle the popup widget
 *
 * @param {OoUiPopupWidget} popupWidget popupWidget from oojs-ui
 * cannot use type because it's not loaded yet
 * @param {number|false} [timeout] use false if user must dismiss it themselves.
 */
function show( popupWidget, timeout = 4000 ) {
	popupWidget.toggle( true );
	// @ts-ignore https://github.com/wikimedia/typescript-types/pull/40
	popupWidget.toggleClipping( true );
	// hide the popup after timeout ms
	if ( timeout === false ) {
		return;
	}
	setTimeout( () => {
		remove( popupWidget );
	}, timeout );
}

/**
 * Remove all popups
 *
 * @param {string} [selector]
 */
function removeAll( selector = '.vector-popup-notification' ) {
	document.querySelectorAll( selector ).forEach( ( element ) => {
		element.remove();
	} );
}

module.exports = {
	add,
	remove,
	removeAll
};
