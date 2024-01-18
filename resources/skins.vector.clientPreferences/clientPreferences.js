/**
 * @typedef {Object} ClientPreference
 * @property {string[]} options that are valid for this client preference
 * @property {string} preferenceKey for registered users.
 */
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
 * @param {Record<string,ClientPreference>} config
 * @return {string[]} of user facing client preferences
 */
function getVisibleClientPreferences( config ) {
	const active = getClientPreferences();
	// Order should be based on key in config.json
	return Object.keys( config ).filter( ( key ) => active.indexOf( key ) > -1 );
}

/**
 * @param {string} featureName
 * @param {string} value
 * @param {Record<string,ClientPreference>} config
 */
function toggleDocClassAndSave( featureName, value, config ) {
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
 * @param {Record<string,ClientPreference>} config
 */
function appendRadioToggle( parent, featureName, value, currentValue, config ) {
	const input = document.createElement( 'input' );
	const name = `skin-client-pref-${ featureName }-group`;
	const id = `skin-client-pref-${ featureName }-value-${ value }`;
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
		toggleDocClassAndSave( featureName, value, config );
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
 * @param {Record<string,ClientPreference>} config
 * @return {Element|null}
 */
function makeClientPreferenceBinaryToggle( featureName, config ) {
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
		appendRadioToggle( form, featureName, value, currentValue, config );
	} );
	row.appendChild( form );
	return row;
}

/**
 * @param {Element} parent
 * @param {string} featureName
 * @param {Record<string,ClientPreference>} config
 */
function makeClientPreference( parent, featureName, config ) {
	// eslint-disable-next-line mediawiki/msg-doc
	const labelMsg = mw.message( `${ featureName }-name` );
	// If the user is not debugging messages and no language exists exit as its a hidden client preference.
	if ( !labelMsg.exists() && mw.config.get( 'wgUserLanguage' ) !== 'qqx' ) {
		return;
	} else {
		const id = `skin-client-prefs-${ featureName }`;
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
		const row = makeClientPreferenceBinaryToggle( featureName, config );
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
 * @param {Record<string,ClientPreference>} config
 * @return {Promise<Node>}
 */
function render( selector, config ) {
	const node = document.querySelector( selector );
	if ( !node ) {
		return Promise.reject();
	}
	return new Promise( ( resolve ) => {
		mw.loader.using( 'codex-styles' ).then( () => {
			getVisibleClientPreferences( config ).forEach( ( pref ) => {
				makeClientPreference( node, pref, config );
			} );
			mw.requestIdleCallback( () => {
				resolve( node );
			} );
		} );
	} );
}

/**
 * @param {string} clickSelector what to click
 * @param {string} renderSelector where to render
 * @param {Record<string,ClientPreference>} config
 */
function bind( clickSelector, renderSelector, config ) {
	let enhanced = false;
	const chk = /** @type {HTMLInputElement} */ (
		document.querySelector( clickSelector )
	);
	if ( !chk ) {
		return;
	}
	if ( chk.checked ) {
		render( renderSelector, config );
		enhanced = true;
	} else {
		chk.addEventListener( 'input', () => {
			if ( enhanced ) {
				return;
			}
			render( renderSelector, config );
			enhanced = true;
		} );
	}
}
module.exports = {
	bind,
	toggleDocClassAndSave,
	render
};
