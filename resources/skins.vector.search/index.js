var
	Vue = require( 'vue' ).default,
	App = require( './App.vue' ).default;

/**
 * @param {HTMLInputElement} search
 * @return {void}
 */
function initApp( search ) {
	var props = {
		searchAccessKey: search.accessKey,
		searchTitle: search.title,
		searchQuery: search.value,
		searchPlaceholder: search.placeholder
	};
	new Vue( { // eslint-disable-line no-new
		components: { App: App },
		el: '#app',
		render: function ( createElement ) {
			return createElement( App, { props: props } );
		}
	} );
}

/**
 * @param {Document} document
 * @return {void}
 */
function main( document ) {
	var
		search = /** @type {HTMLInputElement|null} */ ( document.getElementById( 'searchInput' ) );

	// Suppress development-mode warning message during development.
	Vue.config.productionTip = process.env.NODE_ENV === 'production'; // eslint-disable-line no-undef

	if ( search ) {
		initApp( search );
	}
}

main( document );
