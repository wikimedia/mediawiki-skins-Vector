const popUpNotification = require( '../../resources/skins.vector.js/popupNotification.js' );

/**
 * @type string
 */
let testId;

/**
 * @type string
 */
let testMessage;

/**
 * @type string
 */
let vectorPopupClass;

/**
 * @type {Record<string,OoUiPopupWidget>}
 */
let activeNotification;

describe( 'Popup Notification', () => {
	beforeEach( () => {
		global.window.matchMedia = jest.fn( () => ( {} ) );
		document.body.style = 'direction: ltr';
		jest.spyOn( mw.loader, 'using' )
			.mockImplementation( () => Promise.resolve() );
		testId = 'test-id';
		testMessage = 'test message';
		vectorPopupClass = 'vector-popup-notification';
		activeNotification = [];
	} );

	afterEach( () => {
		jest.resetModules();
	} );

	// test add function
	test( 'add', async () => {
		const popupWidget = await popUpNotification.add(
			document.body,
			testMessage,
			testId,
			[],
			4000,
			() => {}
		);
		activeNotification[ testId ] = popupWidget;
		expect( activeNotification[ testId ] ).toBeDefined();
		expect( activeNotification[ testId ].$element ).toBeDefined();
		expect( activeNotification[ testId ].$element[ 0 ].textContent )
			.toContain( testMessage );
		expect( activeNotification[ testId ].$element[ 0 ].classList
			.contains( vectorPopupClass ) ).toBe( true );
	} );

	// test remove function
	test( 'remove', async () => {
		const popupWidget = await popUpNotification.add(
			document.body,
			testMessage,
			testId,
			[],
			4000,
			() => {}
		);
		activeNotification[ testId ] = popupWidget;
		expect( activeNotification[ testId ].visible ).toBe( true );
		popUpNotification.remove( activeNotification[ testId ] );
		expect( document.body.contains( activeNotification[ testId ].$element[ 0 ] ) )
			.toBe( false );
	} );

	// test show function
	test( 'show', async () => {
		const popupWidget = await popUpNotification.add(
			document.body,
			testMessage,
			testId,
			[],
			4000,
			() => {}
		);
		activeNotification[ testId ] = popupWidget;
		expect( activeNotification[ testId ].visible ).toBe( true );
		activeNotification[ testId ].toggle( false );
		expect( activeNotification[ testId ].visible ).toBe( false );
		popUpNotification.tests.show( activeNotification[ testId ] );
		expect( activeNotification[ testId ].visible ).toBe( true );
	} );

	// test removeAll function
	test( 'removeAll', async () => {
		const popupWidget = await popUpNotification.add(
			document.body,
			testMessage,
			testId,
			[],
			4000,
			() => {}
		);
		activeNotification[ testId ] = popupWidget;
		expect( activeNotification[ testId ].visible ).toBe( true );
		popUpNotification.removeAll();
		expect( document.body.contains( activeNotification[ testId ].$element[ 0 ] ) )
			.toBe( false );
	} );
} );
