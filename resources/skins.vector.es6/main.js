// Enable Vector features limited to ES6 browse
const stickyHeader = require( './stickyHeader.js' ),
	searchToggle = require( './searchToggle.js' );

/**
 * @return {void}
 */
const main = () => {
	// Initialize the search toggle for the main header only. The sticky header
	// toggle is initialized after wvui search loads.
	const searchToggleElement = document.querySelector( '.mw-header .search-toggle' );
	if ( searchToggleElement ) {
		searchToggle( searchToggleElement );
	}
	stickyHeader();
};

if ( document.readyState === 'interactive' || document.readyState === 'complete' ) {
	main();
} else {
	// This is needed when document.readyState === 'loading'.
	document.addEventListener( 'DOMContentLoaded', () => main );
}
