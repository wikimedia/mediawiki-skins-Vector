const features = require( './features.js' );
const LIMITED_WIDTH_FEATURE_NAME = 'limited-width';

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
	const toggle = document.createElement( 'button' );
	toggle.setAttribute( 'title', mw.msg( 'vector-limited-width-toggle' ) );
	toggle.setAttribute( 'aria-hidden', 'true' );
	toggle.textContent = mw.msg( 'vector-limited-width-toggle' );
	toggle.classList.add( 'mw-ui-icon', 'mw-ui-icon-element', 'mw-ui-button', 'vector-limited-width-toggle' );
	setDataAttribute( toggle );
	document.body.appendChild( toggle );
	toggle.addEventListener( 'click', function () {
		features.toggle( LIMITED_WIDTH_FEATURE_NAME );
		setDataAttribute( toggle );
	} );
}

module.exports = init;
