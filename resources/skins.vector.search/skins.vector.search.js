/** @module search */

const
	Vue = require( 'vue' ),
	{
		App,
		restSearchClient,
		urlGenerator
	} = require( /** @type {string} */ ( 'mediawiki.skinning.typeaheadSearch' ) ),
	config = require( './config.json' );

const searchApiUrl = mw.config.get( 'wgVectorSearchApiUrl',
	mw.config.get( 'wgScriptPath' ) + '/rest.php'
);
// The config variables enable customization of the URL generator and search client
// by Wikidata. Note: These must be defined by Wikidata in the page HTML and are not
// read from LocalSettings.php
const urlGeneratorInstance = mw.config.get(
	'wgVectorSearchUrlGenerator',
	urlGenerator( mw.config.get( 'wgScript' ) )
);
const restClient = mw.config.get(
	'wgVectorSearchClient',
	restSearchClient( searchApiUrl, urlGeneratorInstance )
);

/**
 * @param {Element} searchBox
 * @return {void}
 */
function initApp( searchBox ) {
	const searchForm = searchBox.querySelector( '.cdx-search-input' ),
		titleInput = /** @type {HTMLInputElement|null} */ (
			searchBox.querySelector( 'input[name=title]' )
		),
		search = /** @type {HTMLInputElement|null} */ ( searchBox.querySelector( 'input[name=search]' ) ),
		searchPageTitle = titleInput && titleInput.value,
		searchContainer = searchBox.querySelector( '.vector-typeahead-search-container' );

	if ( !searchForm || !search || !titleInput ) {
		throw new Error( 'Attempted to create Vue search element from an incompatible element.' );
	}

	// @ts-ignore MediaWiki-specific function
	Vue.createMwApp(
		App, Object.assign( {
			prefixClass: 'vector-',
			id: searchForm.id,
			autocapitalizeValue: search.getAttribute( 'autocapitalize' ),
			autofocusInput: search === document.activeElement,
			action: searchForm.getAttribute( 'action' ),
			searchAccessKey: search.getAttribute( 'accessKey' ),
			searchPageTitle,
			restClient,
			urlGenerator: urlGeneratorInstance,
			searchTitle: search.getAttribute( 'title' ),
			searchPlaceholder: search.getAttribute( 'placeholder' ),
			searchQuery: search.value,
			autoExpandWidth: searchBox ? searchBox.classList.contains( 'vector-search-box-auto-expand-width' ) : false
		// Pass additional config from server.
		}, config )
	)
		.mount( searchContainer );
}
/**
 * @param {Document} document
 * @return {void}
 */
function main( document ) {
	document.querySelectorAll( '.vector-search-box' )
		.forEach( initApp );
}
main( document );
