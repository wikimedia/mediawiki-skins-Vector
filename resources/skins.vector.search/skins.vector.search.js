/** @module search */
var
	Vue = require( 'vue' ).default || require( 'vue' ),
	App = require( './App.vue' ),
	config = require( './config.json' );

/**
 * @param {HTMLElement} searchForm
 * @param {NodeList} secondarySearchElements
 * @param {HTMLInputElement} search
 * @param {string|null} searchPageTitle title of page used for searching e.g. Special:Search
 *  If null then this will default to Special:Search.
 * @return {void}
 */
function initApp( searchForm, secondarySearchElements, search, searchPageTitle ) {
	/**
	 *
	 * @ignore
	 * @param {Function} createElement
	 * @param {string} id
	 * @return {Vue.VNode}
	 */
	var renderFn = function ( createElement, id ) {
		return createElement( App, {
			props: $.extend( {
				id: id,
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
	};
	// eslint-disable-next-line no-new
	new Vue( {
		el: searchForm,
		render: function ( createElement ) {
			return renderFn( createElement, 'searchform' );
		}
	} );

	// Initialize secondary search elements like the search in the sticky header.
	Array.prototype.forEach.call( secondarySearchElements, function ( secondarySearchElement ) {
		// eslint-disable-next-line no-new
		new Vue( {
			el: secondarySearchElement,
			render: function ( createElement ) {
				return renderFn( createElement, secondarySearchElement.id );
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
		searchForm = /** @type {HTMLElement} */ ( document.querySelector( '#searchform' ) ),
		titleInput = /** @type {HTMLInputElement|null} */ (
			searchForm.querySelector( 'input[name=title]' )
		),
		search = /** @type {HTMLInputElement|null} */ ( document.getElementById( 'searchInput' ) ),
		// Since App.vue requires a unique id prop, only query elements with an id attribute.
		secondarySearchElements = document.querySelectorAll( '.vector-secondary-search[id]' );

	if ( search && searchForm ) {
		initApp( searchForm, secondarySearchElements, search, titleInput && titleInput.value );
	}
}
main( document );
