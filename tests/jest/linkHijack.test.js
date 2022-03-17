const linkHijack = require( '../../resources/skins.vector.es6/linkHijack.js' );

describe( 'linkHijack.js', () => {
	let /** @type {jest.Mock} */ getHrefSpy;
	let /** @type {Function | undefined} */ cleanup;

	beforeEach( () => {
		cleanup = undefined;
		getHrefSpy = jest.fn( () => 'http://localhost/wiki/Tree' );

		// @ts-ignore
		delete window.location;
		// @ts-ignore
		window.location = {
			get href() {
				return getHrefSpy();
			}
		};

		document.body.innerHTML = `
			<a class="anchor" href="/wiki/Barack_Obama">Barack Obama</a>
			<a class="anchor-with-span" href="/wiki/Barack_Obama"><span class="inner-span">Barack Obama</span></a>
			<a class="anchor-with-hash" href="/wiki/Tree#jump">Jump</a>
			<a class="anchor-with-different-origin" href="http://www.google.com/">Different Origin</a>
			<a class="anchor-with-query-param" href="/wiki/Barack_Obama?tableofcontents=1">Query Param</a>
			<a class="anchor-with-different-query-param" href="/wiki/Barack_Obama?foo=1">Query Param</a>
			<a class="anchor-without-href">Anchor without href</a>
			<a class="anchor-with-invalid-href href="invalid">Anchor with invalid href</a>
			<div></div>
			<svg></svg>
		`;
	} );

	afterEach( () => {
		if ( cleanup ) {
			cleanup();
		}
	} );

	describe( 'when link origin is same and pathname is different', () => {
		it( 'appends a query param to anchor element when user clicks anchor element', () => {
			const anchor = /** @type {HTMLAnchorElement} */ ( document.querySelector( '.anchor' ) );
			cleanup = linkHijack( 'tableofcontents', '1' );
			anchor.click();
			expect( anchor.href ).toBe( 'http://localhost/wiki/Barack_Obama?tableofcontents=1' );
		} );

		it( 'appends a query param to anchor element when user clicks inner span element', () => {
			const anchorWithSpan = /** @type {HTMLAnchorElement} */ ( document.querySelector( '.anchor-with-span' ) );
			const innerSpan = /** @type {HTMLSpanElement} */ ( document.querySelector( '.inner-span' ) );
			cleanup = linkHijack( 'tableofcontents', '1' );
			innerSpan.click();
			expect( anchorWithSpan.href ).toBe( 'http://localhost/wiki/Barack_Obama?tableofcontents=1' );
		} );
	} );

	describe( 'when link origin is different and pathname is different', () => {
		it( 'does not append a query param to the anchor element', () => {
			const anchorWithDifferentOrigin = /** @type {HTMLAnchorElement} */ ( document.querySelector( '.anchor-with-different-origin' ) );
			cleanup = linkHijack( 'tableofcontents', '1' );
			anchorWithDifferentOrigin.click();
			expect( anchorWithDifferentOrigin.href ).toBe( 'http://www.google.com/' );
		} );
	} );

	describe( 'when link origin is same and pathname is same and hash fragment is present', () => {
		it( 'does not append a query param to the anchor element', () => {
			const anchorWithHash = /** @type {HTMLAnchorElement} */ ( document.querySelector( '.anchor-with-hash' ) );
			cleanup = linkHijack( 'tableofcontents', '1' );
			anchorWithHash.click();
			expect( anchorWithHash.href ).toBe( 'http://localhost/wiki/Tree#jump' );
		} );
	} );

	describe( 'when link already has the same query param', () => {
		it( 'does not duplicate query params', () => {
			const anchorWithQueryParam = /** @type {HTMLAnchorElement} */ ( document.querySelector( '.anchor-with-query-param' ) );
			cleanup = linkHijack( 'tableofcontents', '1' );
			anchorWithQueryParam.click();
			expect( anchorWithQueryParam.href ).toBe( 'http://localhost/wiki/Barack_Obama?tableofcontents=1' );
		} );
	} );

	describe( 'when link already has different query param', () => {
		it( 'appends query param', () => {
			const anchorWithDifferentQueryParam = /** @type {HTMLAnchorElement} */ ( document.querySelector( '.anchor-with-different-query-param' ) );
			cleanup = linkHijack( 'tableofcontents', '1' );
			anchorWithDifferentQueryParam.click();
			expect( anchorWithDifferentQueryParam.href ).toBe( 'http://localhost/wiki/Barack_Obama?foo=1&tableofcontents=1' );
		} );
	} );

	describe( 'when clicking on an element that is not an anchor element or a child of an anchor', () => {
		it( 'does nothing (no errors)', () => {
			const div = /** @type {HTMLDivElement}} */ ( document.querySelector( 'div' ) );
			cleanup = linkHijack( 'tableofcontents', '1' );
			div.click();
		} );
	} );

	describe( 'when clicking on an element that is not an HTMLElement', () => {
		it( 'does nothing (no errors)', () => {
			const svg = /** @type {SVGElement}} */ ( document.querySelector( 'svg' ) );
			cleanup = linkHijack( 'tableofcontents', '1' );
			svg.dispatchEvent( new Event( 'click', { bubbles: true } ) );
		} );
	} );

	describe( 'when clicking on an Anchor without an `href', () => {
		it( 'handles it gracefully (no errors)', () => {
			const anchor = /** @type {HTMLAnchorElement}} */ ( document.querySelector( '.anchor-without-href' ) );
			cleanup = linkHijack( 'tableofcontents', '1' );
			anchor.click();
		} );
	} );

	describe( 'when clicking on an Anchor with an invalid `href', () => {
		it( 'handles it gracefully (no errors)', () => {
			const anchor = /** @type {HTMLAnchorElement}} */ ( document.querySelector( '.anchor-with-invalid-href' ) );
			cleanup = linkHijack( 'tableofcontents', '1' );
			anchor.click();
		} );
	} );

	describe( 'when cleanup function is called', () => {
		let /** @type {any} */ events;

		beforeEach( () => {
			events = {};

			jest.spyOn( document.body, 'addEventListener' ).mockImplementation( ( event ) => {
				if ( !( event in events ) ) {
					events[ event ] = 1;
				} else {
					events[ event ] += 1;
				}
			} );

			jest.spyOn( document.body, 'removeEventListener' ).mockImplementation( ( event ) => {
				events[ event ] -= 1;
				if ( events[ event ] === 0 ) {
					delete events[ event ];
				}
			} );
		} );

		afterEach( () => {
			jest.restoreAllMocks();
		} );

		it( 'removes added event listeners', () => {
			linkHijack( 'tableofcontents', '1' )();
			linkHijack( 'tableofcontents', '1' )();

			expect( Object.keys( events ).length ).toBe( 0 );
		} );
	} );
} );
