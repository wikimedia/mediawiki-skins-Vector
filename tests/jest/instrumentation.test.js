const instrumentation = require( '../../resources/skins.vector.search/instrumentation.js' );

describe( 'instrumentation', () => {
	test.each( [
		[ 0, 'acrw10' ],
		[ 1, 'acrw11' ],
		[ -1, 'acrw1' ]
	] )( 'getWprovFromResultIndex( %d ) = %s', ( index, expected ) => {
		expect( instrumentation.getWprovFromResultIndex( index ) )
			.toBe( expected );
	} );

	describe( 'generateUrl', () => {
		test.each( [
			[ 'string', 'title' ],
			[ 'object', { title: 'title' } ]
		] )( 'should generate URL from %s', ( _name, suggestion ) => {
			const meta = { index: 1 };
			expect( instrumentation.generateUrl( suggestion, meta ) )
				// mw-node-qunit provides a pretty weird mw.Uri.toString()...
				.toBe( 'https://host?title=suggestion=wprov' );
		} );
	} );
} );
