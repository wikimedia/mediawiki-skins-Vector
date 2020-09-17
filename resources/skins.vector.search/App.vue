<template>
	<div id="app">
		<wvui-input
			id="searchInput"
			ref="input"
			type="search"
			name="search"
			autocomplete="off"
			:accesskey="searchAccessKey"
			:title="searchTitle"
			:placeholder="searchPlaceholder"
			:value="searchQuery"
		></wvui-input>
	</div>
</template>

<script>
var
	Vue = require( 'vue' ).default,
	components = require( '@wikimedia/wvui' ).default;

require( '@wikimedia/wvui/dist/wvui.css' );

module.exports = Vue.extend( {
	name: 'App',
	components: components,
	props: {
		/** The keyboard shortcut to focus search. */
		searchAccessKey: {
			type: String,
			default: undefined
		},
		/** The access key informational tip for search. */
		searchTitle: {
			type: String,
			default: ''
		},
		/** The ghost text shown when no search query is entered. */
		searchPlaceholder: {
			type: String,
			default: ''
		},
		/**
		 * The search query string taken from the server-side rendered input immediately before
		 * client render.
		 */
		searchQuery: {
			type: String,
			default: ''
		}
	}
} );
</script>
