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
				.toBe( 'https://host/?title=Special%3ASearch&suggestion=title&wprov=acrw11' );
		} );
	} );

	test( 'addWprovToSearchResultUrls', () => {
		const url1 = 'https://host/?title=Special%3ASearch&search=Aa',
			url2Base = 'https://host/?title=Special%3ASearch&search=Ab',
			url3 = 'https://host/Ac';
		const results = [
			{
				title: 'Aa',
				url: url1
			},
			{
				title: 'Ab',
				url: `${url2Base}&wprov=xyz`
			},
			{
				title: 'Ac',
				url: url3
			},
			{
				title: 'Ad'
			}
		];

		expect( instrumentation.addWprovToSearchResultUrls( results ) )
			.toStrictEqual( [
				{
					title: 'Aa',
					url: `${url1}&wprov=acrw10`
				},
				{
					title: 'Ab',
					url: `${url2Base}&wprov=acrw11`
				},
				{
					title: 'Ac',
					url: `${url3}?wprov=acrw12`
				},
				{
					title: 'Ad'
				}
			] );
		expect( results[ 0 ].url ).toStrictEqual( url1 );
	} );
} );
