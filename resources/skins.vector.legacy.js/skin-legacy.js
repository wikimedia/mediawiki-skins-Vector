/* eslint-disable no-jquery/no-jquery-constructor */
/** @interface MediaWikiPageReadyModule */
const
	collapsibleTabs = require( './collapsibleTabs.js' ),
	/** @type {MediaWikiPageReadyModule} */
	pageReady = require( /** @type {string} */( 'mediawiki.page.ready' ) ),
	portlets = require( './portlets.js' ),
	vector = require( './vector.js' );

function main() {
	collapsibleTabs.init();
	$( vector.init );
	portlets.main();
	pageReady.loadSearchModule( 'mediawiki.searchSuggest' );
}

main();
