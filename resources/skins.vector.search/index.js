var
	Vue = require( 'vue' ).default,
	App = require( './App.vue' ).default;

/**
 * @return {void}
 */
function initApp() {
	new Vue( { // eslint-disable-line no-new
		components: { App: App },
		el: '#searchInput',
		render: function ( createElement ) {
			return createElement( App );
		}
	} );
}

/**
 * @return {void}
 */
function main() {
	// Suppress development-mode warning message during development.
	Vue.config.productionTip = process.env.NODE_ENV === 'production'; // eslint-disable-line no-undef

	initApp();
}

main();
