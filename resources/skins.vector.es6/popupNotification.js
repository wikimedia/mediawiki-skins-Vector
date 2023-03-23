/**
 * Adds and show a popup to the user to point them to the new location of the element
 *
 * @param {Element} container
 * @param {string} message
 * @param {string[]} [classes]
 * @param {number} [timeout]
 * @param {boolean} [autoClose]
 */
function add( container, message, classes = [], timeout = 4000, autoClose = true ) {
	/**
	 * @type {any}
	 */
	let popupWidget;
	// load oojs-ui if it's not already loaded
	mw.loader.using( 'oojs-ui-core' ).then( () => {
		// @ts-ignore-next-line
		popupWidget = new OO.ui.PopupWidget( {
			$content: $( '<p>' ).text( message ),
			autoClose,
			padded: true,
			anchor: true,
			align: 'center',
			position: 'below',
			classes: [ 'vector-popup-notification' ].concat( classes ),
			container
		} );
		popupWidget.$element.appendTo( container );
		show( popupWidget, timeout );
	} );
}

/**
 * Toggle the popup widget
 *
 * @param {any} popupWidget popupWidget from oojs-ui
 * cannot use type because it's not loaded yet
 * @param {number} [timeout]
 */
function show( popupWidget, timeout = 4000 ) {
	popupWidget.toggle( true );
	popupWidget.toggleClipping( true );
	// hide the popup after timeout ms
	setTimeout( () => {
		popupWidget.toggle( false );
		popupWidget.$element.remove();
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
	removeAll
};
