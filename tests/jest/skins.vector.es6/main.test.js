const { test } = require( '../../../resources/skins.vector.es6/main.js' );

describe( 'main.js', () => {
	it( 'getHeadingIntersectionHandler', () => {
		const section = document.createElement( 'div' );
		section.setAttribute( 'class', 'mw-body-content' );
		section.setAttribute( 'id', 'mw-content-text' );
		const heading = document.createElement( 'h2' );
		const headline = document.createElement( 'span' );
		headline.classList.add( 'mw-headline' );
		headline.setAttribute( 'id', 'headline' );
		heading.appendChild( headline );
		section.appendChild(
			heading
		);

		[
			[ section, 'toc-mw-content-text' ],
			[ heading, 'toc-headline' ]
		].forEach( ( testCase ) => {
			const node = /** @type {HTMLElement} */ ( testCase[ 0 ] );
			const fn = jest.fn();
			const handler = test.getHeadingIntersectionHandler( fn );
			handler( node );
			expect( fn ).toHaveBeenCalledWith( testCase[ 1 ] );
		} );
	} );
} );
