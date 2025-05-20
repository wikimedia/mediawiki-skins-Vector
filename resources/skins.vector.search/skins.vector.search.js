/** @module search */

const
	Vue = require( 'vue' ),
	{
		App,
		restSearchClient,
		urlGenerator
	} = require( /** @type {string} */ ( 'mediawiki.skinning.typeaheadSearch' ) ),
	config = require( './config.json' );

const searchConfig = require( './searchConfig.json' );
const inNamespace = searchConfig.ContentNamespaces.includes( mw.config.get( 'wgNamespaceNumber' ) );
const searchApiUrl = mw.config.get( 'wgVectorSearchApiUrl',
	searchConfig.VectorSearchApiUrl || mw.config.get( 'wgScriptPath' ) + '/rest.php'
);
const recommendationApiUrl = inNamespace ? searchConfig.VectorSearchRecommendationsApiUrl : null;
// The param config must be defined for empty search recommendations to be enabled.
const showEmptySearchRecommendations = inNamespace && recommendationApiUrl !== null;
// The config variables enable customization of the URL generator and search client
// by Wikidata. Note: These must be defined by Wikidata in the page HTML and are not
// read from LocalSettings.php
const urlGeneratorInstance = mw.config.get(
	'wgVectorSearchUrlGenerator',
	urlGenerator( mw.config.get( 'wgScript' ) )
);
const restClient = mw.config.get(
	'wgVectorSearchClient',
	restSearchClient(
		searchApiUrl,
		urlGeneratorInstance,
		recommendationApiUrl
	)
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
			autoExpandWidth: searchBox ? searchBox.classList.contains( 'vector-search-box-auto-expand-width' ) : false,
			showEmptySearchRecommendations
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
