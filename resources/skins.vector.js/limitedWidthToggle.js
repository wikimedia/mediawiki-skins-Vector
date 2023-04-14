const features = require( './features.js' );
const popupNotification = require( './popupNotification.js' );
const config = require( './config.json' );
const LIMITED_WIDTH_FEATURE_NAME = 'limited-width';
const AWARE_COOKIE_NAME = `${LIMITED_WIDTH_FEATURE_NAME}-aware`;
const TOGGLE_ID = 'toggleWidth';

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
	const toggleMenu = document.createElement( 'div' );
	toggleMenu.setAttribute( 'class', 'vector-settings' );
	toggleMenu.appendChild( toggle );
	document.body.appendChild( toggleMenu );
	// @ts-ignore https://github.com/wikimedia/typescript-types/pull/39
	const userMayNotKnowTheyAreInExpandedMode = !mw.cookie.get( AWARE_COOKIE_NAME );
	const dismiss = () => {
		mw.cookie.set( AWARE_COOKIE_NAME, '1' );
	};

	/**
	 * check user has not disabled cookies by
	 * reading the cookie and unsetting the cookie.
	 *
	 * @return {boolean}
	 */
	const areCookiesEnabled = () => {
		dismiss();
		// @ts-ignore https://github.com/wikimedia/typescript-types/pull/39
		const savedSuccessfully = mw.cookie.get( AWARE_COOKIE_NAME ) === '1';
		mw.cookie.set( AWARE_COOKIE_NAME, null );
		return savedSuccessfully;
	};
	/**
	 * @param {string} id this allows us to group notifications making sure only one is visible
	 *  at any given time. All existing popups associated with ID will be removed.
	 * @param {number|false} timeout
	 */
	const showPopup = ( id, timeout = 4000 ) => {
		if ( !config.VectorLimitedWidthIndicator ) {
			return;
		}
		const label = features.isEnabled( LIMITED_WIDTH_FEATURE_NAME ) ?
			'vector-limited-width-toggle-off-popup' : 'vector-limited-width-toggle-on-popup';
		// possible messages:
		// * vector-limited-width-toggle-off-popup
		// * vector-limited-width-toggle-on-popup
		popupNotification.add( toggleMenu, mw.msg( label ), id, [], timeout, dismiss );
	};

	/**
	 * FIXME: This currently loads OOUI on page load. It should be swapped out
	 * for a more performance friendly version before being deployed.
	 * See T334366.
	 */
	const showPageLoadPopups = () => {
		showPopup( TOGGLE_ID, false );
	};

	toggle.addEventListener( 'click', function () {
		features.toggle( LIMITED_WIDTH_FEATURE_NAME );
		setDataAttribute( toggle );
		window.dispatchEvent( new Event( 'resize' ) );
		if ( !features.isEnabled( LIMITED_WIDTH_FEATURE_NAME ) ) {
			showPopup( TOGGLE_ID );
		}
		if ( !features.isEnabled( LIMITED_WIDTH_FEATURE_NAME ) ) {
			dismiss();
		}
	} );

	if ( userMayNotKnowTheyAreInExpandedMode ) {
		if ( areCookiesEnabled() ) {
			showPageLoadPopups();
		}
	}
}

module.exports = init;
