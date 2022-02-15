// @ts-nocheck
const tableOfContents = require( '../../resources/skins.vector.es6/tableOfContents.js' );

const template = `
    <ul>

        <li id="toc-foo" class="sidebar-toc-level-1">
            <a href="#foo">foo</a>
        </li>

        <li id="toc-bar" class="sidebar-toc-level-1">
            <a href="#bar">bar</a>
            <ul>
                <li id="toc-baz">
                    <a href="#baz">baz</a>
                </li>
            </ul>
        </li>

        <li id="toc-qux" class="sidebar-toc-level-1">
            <a href="#qux">qux</a>
        </li>

    </ul>
`;

let toc, fooSection, barSection, bazSection, quxSection;

beforeEach( () => {
	document.body.innerHTML = template;
	toc = tableOfContents( { container: document.body } );
	fooSection = document.getElementById( 'toc-foo' );
	barSection = document.getElementById( 'toc-bar' );
	bazSection = document.getElementById( 'toc-baz' );
	quxSection = document.getElementById( 'toc-qux' );
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
