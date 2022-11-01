const features = require( '../../../resources/skins.vector.js/features.js' );

describe( 'features', () => {
	beforeEach( () => {
		document.body.setAttribute( 'class', 'vector-feature-foo-disabled vector-feature-bar-enabled hello' );
	} );

	test( 'toggle', () => {
		features.toggle( 'foo' );
		features.toggle( 'bar' );

		expect(
			document.body.classList.contains( 'vector-feature-foo-enabled' )
		).toBe( true );
		expect(
			document.body.classList.contains( 'vector-feature-foo-disabled' )
		).toBe( false );
		expect(
			document.body.classList.contains( 'vector-feature-bar-disabled' )
		).toBe( true );
		expect(
			document.body.classList.contains( 'vector-feature-bar-enabled' )
		).toBe( false );
		expect(
			document.body.classList.contains( 'hello' )
		).toBe( true );
	} );

	test( 'toggle unknown feature', () => {
		expect( () => {
			features.toggle( 'unknown' );
		} ).toThrow();
	} );
} );
