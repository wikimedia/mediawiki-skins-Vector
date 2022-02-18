// @ts-nocheck
const mustache = require( 'mustache' );
const fs = require( 'fs' );
const tableOfContentsTemplate = fs.readFileSync( 'includes/templates/TableOfContents.mustache', 'utf8' );
const tableOfContentsTopSectionTemplate = fs.readFileSync( 'includes/templates/TableOfContents__topSection.mustache', 'utf8' );
const tableOfContentsLineTemplate = fs.readFileSync( 'includes/templates/TableOfContents__line.mustache', 'utf8' );
const initTableOfContents = require( '../../resources/skins.vector.es6/tableOfContents.js' );

let fooSection, barSection, bazSection, quxSection;
const onHeadingClick = jest.fn();
const onToggleClick = jest.fn();

/**
 * @param {Object} templateProps
 * @return {string}
 */
function render( templateProps = {} ) {
	const templateData = Object.assign( {
		'vector-is-collapse-sections-enabled': false,
		'array-sections': [ {
			toclevel: 1,
			number: '1',
			line: 'foo',
			anchor: 'foo',
			'array-sections': null
		}, {
			toclevel: 1,
			number: '2',
			line: 'bar',
			anchor: 'bar',
			'array-sections': [ {
				toclevel: 2,
				number: '2.1',
				line: 'baz',
				anchor: 'baz',
				'array-sections': null
			} ]
		}, {
			toclevel: 1,
			number: '3',
			line: 'qux',
			anchor: 'qux',
			'array-sections': null
		} ]
	}, templateProps );

	/* eslint-disable camelcase */
	return mustache.render( tableOfContentsTemplate, templateData, {
		TableOfContents__topSection: tableOfContentsTopSectionTemplate,
		TableOfContents__line: tableOfContentsLineTemplate
	} );
	/* eslint-enable camelcase */
}

/**
 * @param {Object} templateProps
 * @return {initTableOfContents.TableOfContents}
 */
function mount( templateProps = {} ) {
	document.body.innerHTML = render( templateProps );
	const toc = initTableOfContents( {
		container: /** @type {HTMLElement} */ document.getElementById( 'mw-panel-toc' ),
		onHeadingClick,
		onToggleClick
	} );

	fooSection = /** @type {HTMLElement} */ document.getElementById( 'toc-foo' );
	barSection = /** @type {HTMLElement} */ document.getElementById( 'toc-bar' );
	bazSection = /** @type {HTMLElement} */ document.getElementById( 'toc-baz' );
	quxSection = /** @type {HTMLElement} */ document.getElementById( 'toc-qux' );

	return toc;
}

describe( 'Table of contents', () => {
	describe( 'binds event listeners', () => {
		test( 'for onHeadingClick', () => {
			const toc = mount();
			const heading = document.querySelector( `#toc-foo .${toc.LINK_CLASS}` );
			heading.click();

			expect( onToggleClick ).not.toBeCalled();
			expect( onHeadingClick ).toBeCalled();
		} );
		test( 'for onToggleClick', () => {
			const toc = mount();
			const toggle = document.querySelector( `#toc-bar .${toc.TOGGLE_CLASS}` );
			toggle.click();

			expect( onHeadingClick ).not.toBeCalled();
			expect( onToggleClick ).toBeCalled();
		} );
	} );

	describe( 'when changing sections', () => {
		test( 'applies correct class', () => {
			const toc = mount( { 'vector-is-collapse-sections-enabled': true } );
			toc.changeActiveSection( 'toc-foo' );
			expect( fooSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( true );
			expect( barSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );
			expect( bazSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );
			expect( quxSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );

			toc.changeActiveSection( 'toc-bar' );
			expect( fooSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );
			expect( barSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( true );
			expect( bazSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );
			expect( quxSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );

			toc.changeActiveSection( 'toc-baz' );
			expect( fooSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );
			expect( barSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( true );
			expect( bazSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( true );
			expect( quxSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );

			toc.changeActiveSection( 'toc-qux' );
			expect( fooSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );
			expect( barSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );
			expect( bazSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( false );
			expect( quxSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) ).toEqual( true );
		} );
	} );

	describe( 'when `vector-is-collapse-sections-enabled` is false', () => {
		test( 'renders', () => {
			mount();
			expect( document.body.innerHTML ).toMatchSnapshot();
		} );

		test( 'expands sections', () => {
			const toc = mount();
			toc.expandSection( 'toc-foo' );
			expect( fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );
			expect( barSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );
			expect( bazSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( false );
			expect( quxSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );

			toc.expandSection( 'toc-bar' );
			expect( fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );
			expect( barSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );
			expect( bazSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( false );
			expect( quxSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );
		} );

		test( 'toggles expanded sections', () => {
			const toc = mount();
			toc.toggleExpandSection( 'toc-foo' );
			expect( fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( false );

			toc.toggleExpandSection( 'toc-foo' );
			expect( fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );
		} );
	} );

	describe( 'when `vector-is-collapse-sections-enabled` is true', () => {
		test( 'renders', () => {
			mount( { 'vector-is-collapse-sections-enabled': true } );
			expect( document.body.innerHTML ).toMatchSnapshot();
		} );

		test( 'expands sections', () => {
			const toc = mount( { 'vector-is-collapse-sections-enabled': true } );
			toc.expandSection( 'toc-foo' );
			expect( fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );
			expect( barSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( false );
			expect( bazSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( false );
			expect( quxSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( false );

			toc.expandSection( 'toc-bar' );
			expect( fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );
			expect( barSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );
			expect( bazSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( false );
			expect( quxSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( false );
		} );

		test( 'toggles expanded sections', () => {
			const toc = mount( { 'vector-is-collapse-sections-enabled': true } );
			toc.toggleExpandSection( 'toc-foo' );
			expect( fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( true );

			toc.toggleExpandSection( 'toc-foo' );
			expect( fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) ).toEqual( false );
		} );

	} );
} );
