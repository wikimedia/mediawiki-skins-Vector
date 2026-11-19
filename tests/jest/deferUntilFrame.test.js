const deferUntilFrame = require( '../../resources/skins.vector.js/deferUntilFrame.js' );

describe( 'deferUntilFrame.js', () => {
	let /** @type {jest.SpyInstance} */ spy;

	beforeEach( () => {
		spy = jest.spyOn( window, 'requestAnimationFrame' ).mockImplementation( ( cb ) => {
			setTimeout( () => {
				cb( 1 );
			} );

			return 1;
		} );
	} );

	afterEach( () => {
		spy.mockRestore();
	} );

	it( 'does not fire rAF if `0` is passed', () => new Promise( ( resolve ) => {
		deferUntilFrame( () => {
			expect( spy ).toHaveBeenCalledTimes( 0 );
			resolve();
		}, 0 );
	} ) );

	it( 'fires rAF the specified number of times', () => new Promise( ( resolve ) => {
		deferUntilFrame( () => {
			expect( spy ).toHaveBeenCalledTimes( 3 );
			resolve();
		}, 3 );
	} ) );
} );
