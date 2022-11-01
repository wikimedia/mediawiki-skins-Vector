var features = require( './features.js' );

/**
 * adds a toggle button
 */
function init() {
	var toggle = document.createElement( 'div' );
	toggle.classList.add( 'mw-ui-icon', 'mw-ui-icon-element', 'mw-ui-button', 'vector-limited-width-toggle' );
	document.body.appendChild( toggle );
	toggle.addEventListener( 'click', function () {
		features.toggle( 'limited-width' );
	} );
}

module.exports = init;
