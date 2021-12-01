/** @module search */

var
	Vue = require( 'vue' ).default || require( 'vue' ),
	App = require( './App.vue' ),
	config = require( './config.json' );

/**
 * @param {Element} searchForm
 * @return {void}
 */
function initApp( searchForm ) {
	var
		titleInput = /** @type {HTMLInputElement|null} */ (
			searchForm.querySelector( 'input[name=title]' )
		),
		search = /** @type {HTMLInputElement|null} */ ( searchForm.querySelector( 'input[name="search"]' ) ),
		searchPageTitle = titleInput && titleInput.value;

	if ( !search || !titleInput ) {
		throw new Error( 'Attempted to create Vue search element from an incompatible element.' );
	}

	// @ts-ignore
	Vue.createMwApp(
		App, $.extend( {
			id: searchForm.id,
			autofocusInput: search === document.activeElement,
			action: searchForm.getAttribute( 'action' ),
			searchAccessKey: search.getAttribute( 'accessKey' ),
			searchPageTitle: searchPageTitle,
			searchTitle: search.getAttribute( 'title' ),
			searchPlaceholder: search.getAttribute( 'placeholder' ),
			searchQuery: search.value
		// Pass additional config from server.
		}, config )
	)
		.mount( searchForm.parentNode );
}
/**
 * @param {Document} document
 * @return {void}
 */
function main( document ) {
	var
		searchForms = document.querySelectorAll( '.vector-search-box-form' );

	searchForms.forEach( function ( searchForm ) {
		initApp( searchForm );
	} );
}
main( document );
