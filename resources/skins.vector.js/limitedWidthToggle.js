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
 * Gets appropriate popup text based off the limited width feature flag
 *
 * @return {string}
 */
function getPopupText() {
	const label = features.isEnabled( LIMITED_WIDTH_FEATURE_NAME ) ?
		'vector-limited-width-toggle-off-popup' : 'vector-limited-width-toggle-on-popup';
	// possible messages:
	// * vector-limited-width-toggle-off-popup
	// * vector-limited-width-toggle-on-popup
	return mw.msg( label );
}

/**
 * adds a toggle button
 */
function init() {
	let settings = /** @type {HTMLElement} */ ( document.querySelector( '.vector-settings' ) );
	let toggle = /** @type {HTMLElement} */ ( document.querySelector( '.vector-limited-width-toggle' ) );
	if ( !( toggle && settings ) ) {
		// FIXME: Replace this block with a return statement after caching implications are resolved
		toggle = document.createElement( 'button' );
		toggle.setAttribute( 'title', mw.msg( 'vector-limited-width-toggle' ) );
		toggle.setAttribute( 'aria-hidden', 'true' );
		toggle.textContent = mw.msg( 'vector-limited-width-toggle' );
		toggle.classList.add( 'mw-ui-icon', 'mw-ui-icon-element', 'mw-ui-button', 'vector-limited-width-toggle' );
		settings = document.createElement( 'div' );
		settings.setAttribute( 'class', 'vector-settings' );
		settings.appendChild( toggle );
		document.body.appendChild( settings );
	}

	setDataAttribute( toggle );
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
		popupNotification.add( settings, getPopupText(), id, [], timeout, dismiss );
	};

	/**
	 * FIXME: This currently loads OOUI on page load. It should be swapped out
	 * for a more performance friendly version before being deployed.
	 * See T334366.
	 */
	const showPageLoadPopups = () => {
		if ( !config.VectorLimitedWidthIndicator ) {
			return;
		}
		addLimitedWidthPopup( settings, getPopupText(), dismiss );
	};

	toggle.addEventListener( 'click', function () {
		features.toggle( LIMITED_WIDTH_FEATURE_NAME );
		setDataAttribute( toggle );
		window.dispatchEvent( new Event( 'resize' ) );
		if ( !features.isEnabled( LIMITED_WIDTH_FEATURE_NAME ) ) {
			showPopup( TOGGLE_ID );
		}
	} );

	if ( userMayNotKnowTheyAreInExpandedMode ) {
		if ( areCookiesEnabled() ) {
			showPageLoadPopups();
		}
	}
}
/**
 * @param {HTMLElement} container
 * @param {string} message
 * @param {Function} [onDismiss]
 */
function addLimitedWidthPopup( container, message, onDismiss = () => {} ) {
	const popupTemplateString = `
		<div class="vector-limited-width-popup">
			<div class="vector-limited-width-popup-head">
				<button class="vector-limited-width-popup-close-button mw-ui-button mw-ui-quiet mw-ui-icon-element">
					<span class="mw-ui-icon mw-ui-icon-wikimedia-close"></span>
					<span>Close</span>
				</button>
			</div>
			<div class="vector-limited-width-popup-body">
				<p>${message}</p>
			</div>
			<div class="vector-limited-width-popup-anchor"></div>
		</div>
	`;
	const popupFrag = document.createRange().createContextualFragment( popupTemplateString );
	container.appendChild( popupFrag );
	const closeButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-limited-width-popup-close-button' ) );
	closeButton.addEventListener( 'click', () => {
		const popup = /** @type {HTMLElement} */ ( document.querySelector( '.vector-limited-width-popup' ) );
		if ( popup && popup.parentElement ) {
			popup.parentElement.removeChild( popup );
		}
		onDismiss();
	}, { once: true } );
}

module.exports = init;
