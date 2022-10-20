const mustache = require( 'mustache' );
const fs = require( 'fs' );
const pinnableHeaderTemplate = fs.readFileSync( 'includes/templates/PinnableHeader.mustache', 'utf8' );
const pinnableHeader = require( '../../resources/skins.vector.es6/pinnableHeader.js' );

const simpleData = {
	'is-pinned': false,
	'data-name': 'simple',
	'data-pinnable-element-id': 'pinnable-element',
	label: 'simple pinnable element',
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
		pinnableHeader.initPinnableHeader();
		const pinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-pin-button' ) );
		const unpinButton = /** @type {HTMLElement} */ ( document.querySelector( '.vector-pinnable-header-unpin-button' ) );
		const header = /** @type {HTMLElement} */ ( document.querySelector( `.${simpleData[ 'data-name' ]}-pinnable-header` ) );

		expect( header.classList.contains( pinnableHeader.PINNED_HEADER_CLASS ) ).toBe( false );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-pinned` ) ).toBe( false );
		expect( header.classList.contains( pinnableHeader.UNPINNED_HEADER_CLASS ) ).toBe( true );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-unpinned` ) ).toBe( true );
		pinButton.click();
		expect( header.classList.contains( pinnableHeader.PINNED_HEADER_CLASS ) ).toBe( true );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-pinned` ) ).toBe( true );
		expect( header.classList.contains( pinnableHeader.UNPINNED_HEADER_CLASS ) ).toBe( false );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-unpinned` ) ).toBe( false );
		unpinButton.click();
		expect( header.classList.contains( pinnableHeader.PINNED_HEADER_CLASS ) ).toBe( false );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-pinned` ) ).toBe( false );
		expect( header.classList.contains( pinnableHeader.UNPINNED_HEADER_CLASS ) ).toBe( true );
		expect( document.body.classList.contains( `${simpleData[ 'data-name' ]}-unpinned` ) ).toBe( true );
	} );

	test( 'doesnt move pinnable element when data attributes arent defined', () => {
		initializeHTML( simpleData );
		pinnableHeader.initPinnableHeader();
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
		pinnableHeader.initPinnableHeader();
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
} );
