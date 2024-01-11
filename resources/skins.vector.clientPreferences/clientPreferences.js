/**
 * @typedef {Object} ClientPreference
 * @property {string[]} options that are valid for this client preference
 * @property {string} preferenceKey for registered users.
 */
const config = /** @type {Record<string,ClientPreference>} */( require( './config.json' ) );
let /** @type {MwApi} */ api;
/**
 * @typedef {Object} PreferenceOption
 * @property {string} label
 * @property {string} value
 *
 */

/**
 * Get the list of client preferences that are active on the page, including hidden.
 *
 * @return {string[]} of active client preferences
 */
function getClientPreferences() {
	return Array.from( document.documentElement.classList ).filter(
		( className ) => className.match( /-clientpref-/ )
	).map( ( className ) => className.split( '-clientpref-' )[ 0 ] );
}

/**
 * Get the list of client preferences that are active on the page and not hidden.
 *
 * @return {string[]} of user facing client preferences
 */
function getVisibleClientPreferences() {
	const active = getClientPreferences();
	// Order should be based on key in config.json
	return Object.keys( config ).filter( ( key ) => active.indexOf( key ) > -1 );
}

/**
 * @param {string} featureName
 * @param {string} value
 */
function toggleDocClassAndSave( featureName, value ) {
	const pref = config[ featureName ];
	if ( mw.user.isNamed() ) {
		// FIXME: Ideally this would be done in mw.user.clientprefs API.
		// mw.user.clientPrefs.get is marked as being only stable for anonymous and temporary users.
		// So instead we have to keep track of all the different possible values and remove them
		// before adding the new class.
		config[ featureName ].options.forEach( ( possibleValue ) => {
			document.documentElement.classList.remove( `${ featureName }-clientpref-${ possibleValue }` );
		} );
		document.documentElement.classList.add( `${ featureName }-clientpref-${ value }` );
		// Ideally this should be taken care of via a single core helper function.
		mw.util.debounce( function () {
			api = api || new mw.Api();
			api.saveOption( pref.preferenceKey, value );
		}, 100 )();
		// END FIXME.
	} else {
		// This case is much simpler - the API  transparently takes care of classes as well as storage.
		mw.user.clientPrefs.set( featureName, value );
	}
}

/**
 * @param {Element} parent
 * @param {string} featureName
 * @param {string} value
 * @param {string} currentValue
 */
function appendRadioToggle( parent, featureName, value, currentValue ) {
	const input = document.createElement( 'input' );
	const name = `vector-client-pref-${ featureName }-group`;
	const id = `vector-client-pref-${ featureName }-value-${ value }`;
	input.name = name;
	input.id = id;
	input.type = 'radio';
	input.value = value;
	input.classList.add( 'cdx-radio__input' );
	if ( currentValue === value ) {
		input.checked = true;
	}
	const icon = document.createElement( 'span' );
	icon.classList.add( 'cdx-radio__icon' );
	const label = document.createElement( 'label' );
	label.classList.add( 'cdx-radio__label' );
	// eslint-disable-next-line mediawiki/msg-doc
	label.textContent = mw.msg( `${ featureName }-${ value }-label` );
	label.setAttribute( 'for', id );
	const container = document.createElement( 'div' );
	container.classList.add( 'cdx-radio' );
	input.setAttribute( 'data-event-name', id );
	container.appendChild( input );
	container.appendChild( icon );
	container.appendChild( label );
	parent.appendChild( container );
	input.addEventListener( 'change', () => {
		toggleDocClassAndSave( featureName, value );
	} );
}

/**
 * @param {string} className
 * @return {Element}
 */
function createRow( className ) {
	const row = document.createElement( 'div' );
	row.setAttribute( 'class', className );
	return row;
}

/**
 * adds a toggle button
 *
 * @param {string} featureName
 * @return {Element|null}
 */
function makeClientPreferenceBinaryToggle( featureName ) {
	const pref = config[ featureName ];
	if ( !pref ) {
		return null;
	}
	const currentValue = mw.user.clientPrefs.get( featureName );
	// The client preference was invalid. This shouldn't happen unless a gadget
	// or script has modified the documentElement.
	if ( typeof currentValue === 'boolean' ) {
		return null;
	}
	const row = createRow( '' );
	const form = document.createElement( 'form' );
	pref.options.forEach( ( value ) => {
		appendRadioToggle( form, featureName, value, currentValue );
	} );
	row.appendChild( form );
	return row;
}

/**
 * @param {Element} parent
 * @param {string} featureName
 */
function makeClientPreference( parent, featureName ) {
	// eslint-disable-next-line mediawiki/msg-doc
	const labelMsg = mw.message( `${ featureName }-name` );
	// If the user is not debugging messages and no language exists exit as its a hidden client preference.
	if ( !labelMsg.exists() && mw.config.get( 'wgUserLanguage' ) !== 'qqx' ) {
		return;
	} else {
		const id = `vector-client-prefs-${ featureName }`;
		// @ts-ignore TODO: upstream patch URL
		const portlet = mw.util.addPortlet( id, labelMsg.text() );
		// eslint-disable-next-line mediawiki/msg-doc
		const descriptionMsg = mw.message( `${ featureName }-description` );
		if ( descriptionMsg.exists() ) {
			const desc = document.createElement( 'div' );
			desc.classList.add( 'mw-portlet-description' );
			desc.textContent = descriptionMsg.text();
			const refNode = portlet.querySelector( 'label' );
			if ( refNode && refNode.parentNode ) {
				refNode.parentNode.insertBefore( desc, refNode.nextSibling );
			}
		}
		const row = makeClientPreferenceBinaryToggle( featureName );
		parent.appendChild( portlet );
		if ( row ) {
			const tmp = mw.util.addPortletLink( id, '', '' );
			// create a dummy link
			if ( tmp ) {
				const link = tmp.querySelector( 'a' );
				if ( link ) {
					link.replaceWith( row );
				}
			}
		}
	}
}

/**
 * Fills the client side preference dropdown with controls.
 * @param {string} selector of element to fill with client preferences
 */
function render( selector ) {
	const node = document.querySelector( selector );
	if ( !node ) {
		return;
	}
	// FIXME: Loading codex-styles is a performance problem. This is only acceptable for logged in users so guard
	// against unexpected use.
	if ( mw.user.isAnon() ) {
		throw new Error( 'T335317: Unexpected state expected. This will cause a performance problem.' );
	}
	getVisibleClientPreferences().forEach( ( pref ) => {
		mw.loader.using( 'codex-styles' ).then( () => {
			mw.requestIdleCallback( () => {
				makeClientPreference( node, pref );
			} );
		} );
	} );
}

/**
 * @param {string} clickSelector what to click
 * @param {string} renderSelector where to render
 */
function bind( clickSelector, renderSelector ) {
	let enhanced = false;
	const chk = /** @type {HTMLInputElement} */ (
		document.querySelector( clickSelector )
	);
	if ( !chk ) {
		return;
	}
	if ( chk.checked ) {
		render( renderSelector );
		enhanced = true;
	} else {
		chk.addEventListener( 'input', () => {
			if ( enhanced ) {
				return;
			}
			render( renderSelector );
			enhanced = true;
		} );
	}
}
module.exports = {
	bind,
	toggleDocClassAndSave,
	render
};
