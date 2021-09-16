/** @module search */
var
	Vue = require( 'vue' ).default || require( 'vue' ),
	App = require( './App.vue' ),
	config = require( './config.json' );

/**
 * @param {Function} createElement
 * @param {Element} searchForm
 * @return {Vue.VNode}
 * @throws {Error} if the searchForm does not
 *  contain input[name=title] and input[name="search"] elements.
 */
function renderFn( createElement, searchForm ) {
	var
		titleInput = /** @type {HTMLInputElement|null} */ (
			searchForm.querySelector( 'input[name=title]' )
		),
		search = /** @type {HTMLInputElement|null} */ ( searchForm.querySelector( 'input[name="search"]' ) ),
		searchPageTitle = titleInput && titleInput.value;

	if ( !search || !titleInput ) {
		throw new Error( 'Attempted to create Vue search element from an incompatible element.' );
	}

	return createElement( App, {
		props: $.extend( {
			id: searchForm.id,
			autofocusInput: search === document.activeElement,
			action: searchForm.getAttribute( 'action' ),
			searchAccessKey: search.getAttribute( 'accessKey' ),
			searchPageTitle: searchPageTitle,
			searchTitle: search.getAttribute( 'title' ),
			searchPlaceholder: search.getAttribute( 'placeholder' ),
			searchQuery: search.value
		},
		// Pass additional config from server.
		config
		)
	} );
}

/**
 * @param {NodeList} searchForms
 * @return {void}
 */
function initApp( searchForms ) {
	searchForms.forEach( function ( searchForm ) {
		// eslint-disable-next-line no-new
		new Vue( {
			el: /** @type {Element} */ ( searchForm ),
			render: function ( createElement ) {
				return renderFn( createElement, /** @type {Element} */ ( searchForm ) );
			}
		} );
	} );
}
/**
 * @param {Document} document
 * @return {void}
 */
function main( document ) {
	var
		// FIXME: Use .vector-search-box-form instead when cache allows.
		searchForms = document.querySelectorAll( '.vector-search-box form' );

	initApp( searchForms );
}
main( document );
