var features = require( './features.js' );
var LIMITED_WIDTH_FEATURE_NAME = 'limited-width';

/**
 * Sets data attribute for click tracking purposes.
 *
 * @param {HTMLElement} toggleBtn
 */
function setDataAttribute( toggleBtn ) {
	toggleBtn.dataset.eventName = features.isEnabled( LIMITED_WIDTH_FEATURE_NAME ) ?
		'limited-width-toggle-off' : 'limited-width-toggle-on';
}
/**
 * adds a toggle button
 */
function init() {
	var toggle = document.createElement( 'button' );
	toggle.classList.add( 'mw-ui-icon', 'mw-ui-icon-element', 'mw-ui-button', 'vector-limited-width-toggle' );
	setDataAttribute( toggle );
	document.body.appendChild( toggle );
	toggle.addEventListener( 'click', function () {
		features.toggle( LIMITED_WIDTH_FEATURE_NAME );
		setDataAttribute( toggle );
	} );
}

module.exports = init;
