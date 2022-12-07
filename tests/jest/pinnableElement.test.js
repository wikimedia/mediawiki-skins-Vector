jest.mock( '../../resources/skins.vector.es6/features.js' );

const features = require( '../../resources/skins.vector.es6/features.js' );
const mustache = require( 'mustache' );
const fs = require( 'fs' );
const pinnableHeaderTemplate = fs.readFileSync( 'includes/templates/PinnableHeader.mustache', 'utf8' );
const pinnableElement = require( '../../resources/skins.vector.es6/pinnableElement.js' );

const simpleData = {
	'is-pinned': false,
	'data-name': 'simple',
	'data-pinnable-element-id': 'pinnable-element',
	label: 'simple pinnable element',
	'label-tag-name': 'div',
	'pin-label': 'pin',
	'unpin-label': 'unpin'
};

const movableData = {
	'is-pinned': false,
	'data-name': 'movable',
	'data-pinnable-element-id': 'pinnable-element',
	'data-pinned-container-id': 'pinned-container',
	'data-unpinned-container-id': 'unpinned-container',
	label: 'moveable pinnable element',
	'label-tag-name': 'div',
	'pin-label': 'pin',
	'unpin-label': 'unpin'
};

// @ts-ignore
const initializeHTML = ( headerData ) => {
	const pinnableHeaderHTML = mustache.render( pinnableHeaderTemplate, headerData );
	const pinnableElementHTML = `<div id="pinnable-element"> ${ pinnableHeaderHTML } </div>`;
	document.body.innerHTML = `
		<div id="pinned-container">
			${ headerData[ 'is-pinned' ] ? pinnableElementHTML : '' }
		</div>
		<div id="unpinned-container">
			${ !headerData[ 'is-pinned' ] ? pinnableElementHTML : '' }
		</div>
	`;

	if ( headerData[ 'data-feature-name' ] ) {
		// Return early if the persistent option is enabled as features.js will
		// manage the body classes instead of pinnableElement.
		return;
	}

	if ( headerData[ 'is-pinned' ] ) {
		document.body.classList.add( `${headerData[ 'data-name' ]}-pinned` );
		document.body.classList.remove( `${headerData[ 'data-name' ]}-unpinned` );
	} else {
		document.body.classList.remove( `${headerData[ 'data-name' ]}-pinned` );
		document.body.classList.add( `${headerData[ 'data-name' ]}-unpinned` );
	}
};

describe( 'Pinnable header', () => {
	test( 'renders', () => {
		initializeHTML( simpleData );
		expect( document.body.innerHTML ).toMatchSnapshot();
	} );

	test( 'updates pinnable classes when toggle is pressed', () => {
		initializeHTML( simpleData );
		pinnableElement.initPinnableElement();
		const pinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-pin-button' ) );
		const unpinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-unpin-button' ) );
		const header = /** @type {HTMLElement} */ ( document.querySelector( `.${simpleData[ 'data-name' ]}-pinnable-header` ) );

		expect( header.classList.contains( pinnableElement.PINNED_HEADER_CLASS ) ).toBe( false );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-pinned` ) ).toBe( false );
		expect( header.classList.contains( pinnableElement.UNPINNED_HEADER_CLASS ) ).toBe( true );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-unpinned` ) ).toBe( true );
		pinButton.click();
		expect( header.classList.contains( pinnableElement.PINNED_HEADER_CLASS ) ).toBe( true );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-pinned` ) ).toBe( true );
		expect( header.classList.contains( pinnableElement.UNPINNED_HEADER_CLASS ) ).toBe( false );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-unpinned` ) ).toBe( false );
		unpinButton.click();
		expect( header.classList.contains( pinnableElement.PINNED_HEADER_CLASS ) ).toBe( false );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-pinned` ) ).toBe( false );
		expect( header.classList.contains( pinnableElement.UNPINNED_HEADER_CLASS ) ).toBe( true );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-unpinned` ) ).toBe( true );
	} );

	test( 'doesnt move pinnable element when data attributes arent defined', () => {
		initializeHTML( simpleData );
		pinnableElement.initPinnableElement();
		const pinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-pin-button' ) );
		const unpinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-unpin-button' ) );
		const pinnableElem = /** @type {HTMLElement} */ ( document.getElementById( simpleData[ 'data-pinnable-element-id' ] ) );

		/* eslint-disable no-restricted-properties */
		expect( pinnableElem.parentElement && pinnableElem.parentElement.id ).toBe( 'unpinned-container' );
		pinButton.click();
		expect( pinnableElem.parentElement && pinnableElem.parentElement.id ).toBe( 'unpinned-container' );
		unpinButton.click();
		expect( pinnableElem.parentElement && pinnableElem.parentElement.id ).toBe( 'unpinned-container' );
		/* eslint-enable no-restricted-properties */
	} );

	test( 'moves pinnable element when data attributes are defined', () => {
		initializeHTML( movableData );
		pinnableElement.initPinnableElement();
		const pinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-pin-button' ) );
		const unpinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-unpin-button' ) );
		const pinnableElem = /** @type {HTMLElement} */ ( document.getElementById( movableData[ 'data-pinnable-element-id' ] ) );

		/* eslint-disable no-restricted-properties */
		expect( pinnableElem.parentElement && pinnableElem.parentElement.id ).toBe( 'unpinned-container' );
		pinButton.click();
		expect( pinnableElem.parentElement && pinnableElem.parentElement.id ).toBe( 'pinned-container' );
		unpinButton.click();
		expect( pinnableElem.parentElement && pinnableElem.parentElement.id ).toBe( 'unpinned-container' );
		/* eslint-enable no-restricted-properties */
	} );

	test( 'calls features.js when data-feature-name is set', () => {
		initializeHTML( {
			...simpleData,
			'data-name': 'vector-page-tools',
			'data-feature-name': 'page-tools-pinned'
		} );
		pinnableElement.initPinnableElement();
		const pinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-pin-button' ) );
		const unpinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-unpin-button' ) );

		pinButton.click();

		expect( features.toggle ).toHaveBeenCalledTimes( 1 );
		expect( features.toggle ).toHaveBeenCalledWith( 'page-tools-pinned' );

		// @ts-ignore
		features.toggle.mockClear();
		unpinButton.click();

		expect( features.toggle ).toHaveBeenCalledTimes( 1 );
		expect( features.toggle ).toHaveBeenCalledWith( 'page-tools-pinned' );
	} );

	test( 'isPinned() returns whether the element is pinned or not', () => {
		initializeHTML( simpleData );
		pinnableElement.initPinnableElement();
		const pinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-pin-button' ) );
		const unpinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-unpin-button' ) );
		const header = /** @type {HTMLElement} */ ( document.querySelector( `.${simpleData[ 'data-name' ]}-pinnable-header` ) );

		pinButton.click();

		expect( pinnableElement.isPinned( header ) ).toBe( true );

		unpinButton.click();

		expect( pinnableElement.isPinned( header ) ).toBe( false );
	} );
} );
