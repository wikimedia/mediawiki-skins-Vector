// Assume every file with a .vue extension is of type Vue.
declare module '*.vue' {
	import Vue from 'vue';
	export default Vue;
}
