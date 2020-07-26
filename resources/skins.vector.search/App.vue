<template>
	<div>Vue.js search</div>
</template>

<script>
var
	Vue = require( 'vue' ).default,
	components = require( '@wikimedia/wvui' ).default;

require( '@wikimedia/wvui/dist/wvui.css' );

module.exports = Vue.extend( {
	name: 'App',
	components: components
} );
</script>
