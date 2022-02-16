// @ts-nocheck
const mustache = require( 'mustache' );
const fs = require( 'fs' );
const tableOfContentsTemplate = fs.readFileSync( 'includes/templates/TableOfContents.mustache', 'utf8' );
const tableOfContentsTopSectionTemplate = fs.readFileSync( 'includes/templates/TableOfContents__topSection.mustache', 'utf8' );
const tableOfContentsLineTemplate = fs.readFileSync( 'includes/templates/TableOfContents__line.mustache', 'utf8' );
const initTableOfContents = require( '../../resources/skins.vector.es6/tableOfContents.js' );

let toc, fooSection, barSection, bazSection, quxSection;
const onHeadingClick = jest.fn();
const onToggleClick = jest.fn();

const templateData = {
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
};

/* eslint-disable camelcase */
const renderedHTML = mustache.render( tableOfContentsTemplate, templateData, {
	TableOfContents__topSection: tableOfContentsTopSectionTemplate,
	TableOfContents__line: tableOfContentsLineTemplate
} );
/* eslint-enable camelcase */

beforeEach( () => {
	document.body.innerHTML = renderedHTML;
	toc = initTableOfContents( {
		container: /** @type {HTMLElement} */ document.getElementById( 'mw-panel-toc' ),
		onHeadingClick,
		onToggleClick
	} );
	fooSection = /** @type {HTMLElement} */ document.getElementById( 'toc-foo' );
	barSection = /** @type {HTMLElement} */ document.getElementById( 'toc-bar' );
	bazSection = /** @type {HTMLElement} */ document.getElementById( 'toc-baz' );
	quxSection = /** @type {HTMLElement} */ document.getElementById( 'toc-qux' );
} );

test( 'Table of contents renders', () => {
	expect( document.body.innerHTML ).toMatchSnapshot();
} );

test( 'Table of contents changes the active sections', () => {
	toc.changeActiveSection( 'toc-foo' );
	expect(
		fooSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		!barSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		!bazSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		!quxSection.classList.contains( toc.ACTIVE_SECTION_CLASS )
	).toEqual( true );

	toc.changeActiveSection( 'toc-bar' );
	expect(
		!fooSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		barSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		!bazSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		!quxSection.classList.contains( toc.ACTIVE_SECTION_CLASS )
	).toEqual( true );

	toc.changeActiveSection( 'toc-baz' );
	expect(
		!fooSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		barSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		bazSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		!quxSection.classList.contains( toc.ACTIVE_SECTION_CLASS )
	).toEqual( true );

	toc.changeActiveSection( 'toc-qux' );
	expect(
		!fooSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		!barSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		!bazSection.classList.contains( toc.ACTIVE_SECTION_CLASS ) &&
		quxSection.classList.contains( toc.ACTIVE_SECTION_CLASS )
	).toEqual( true );
} );

test( 'Table of contents expands sections', () => {
	toc.expandSection( 'toc-foo' );
	expect(
		fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) &&
		!barSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) &&
		!bazSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) &&
		!quxSection.classList.contains( toc.EXPANDED_SECTION_CLASS )
	).toEqual( true );

	toc.expandSection( 'toc-bar' );
	expect(
		fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) &&
		barSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) &&
		!bazSection.classList.contains( toc.EXPANDED_SECTION_CLASS ) &&
		!quxSection.classList.contains( toc.EXPANDED_SECTION_CLASS )
	).toEqual( true );
} );

test( 'Table of contents toggles expanded sections', () => {
	toc.toggleExpandSection( 'toc-foo' );
	expect(
		fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS )
	).toEqual( true );

	toc.toggleExpandSection( 'toc-foo' );
	expect(
		fooSection.classList.contains( toc.EXPANDED_SECTION_CLASS )
	).toEqual( false );
} );

describe( 'Table of contents binds event listeners', () => {
	test( 'for onHeadingClick', () => {
		const heading = document.querySelector( `#toc-foo .${toc.LINK_CLASS}` );
		heading.click();

		expect( onToggleClick ).not.toBeCalled();
		expect( onHeadingClick ).toBeCalled();
	} );
	test( 'for onToggleClick', () => {
		const toggle = document.querySelector( `#toc-bar .${toc.TOGGLE_CLASS}` );
		toggle.click();

		expect( onHeadingClick ).not.toBeCalled();
		expect( onToggleClick ).toBeCalled();
	} );
} );
