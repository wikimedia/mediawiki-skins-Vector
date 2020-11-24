/* global FetchEndEvent, SuggestionClickEvent, SubmitEvent */
/** @module Instrumentation */

/**
 * The value of the `inputLocation` property of any and all SearchSatisfaction events sent by the
 * corresponding instrumentation.
 *
 * @see https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/skins/Vector/+/refs/heads/master/includes/Constants.php
 */
var INPUT_LOCATION_MOVED = 'header-moved',
	wgScript = mw.config.get( 'wgScript' );

/**
 * @param {FetchEndEvent} event
 */
function onFetchEnd( event ) {
	mw.track( 'mediawiki.searchSuggest', {
		action: 'impression-results',
		numberOfResults: event.numberOfResults,
		// resultSetType: '',
		// searchId: '',
		query: event.query,
		inputLocation: INPUT_LOCATION_MOVED
	} );
}

/**
 * @param {SuggestionClickEvent|SubmitEvent} event
 */
function onSuggestionClick( event ) {
	mw.track( 'mediawiki.searchSuggest', {
		action: 'click-result',
		numberOfResults: event.numberOfResults,
		index: event.index
	} );
}

/**
 * Generates the value of the `wprov` parameter to be used in the URL of a search result and the
 * `wprov` hidden input.
 *
 * See https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/extensions/WikimediaEvents/+/refs/heads/master/modules/ext.wikimediaEvents/searchSatisfaction.js
 * and also the top of that file for additional detail about the shape of the parameter.
 *
 * @param {number} index
 * @return {string}
 */
function getWprovFromResultIndex( index ) {

	// If the user hasn't highlighted an autocomplete result.
	if ( index === -1 ) {
		return 'acrw1';
	}

	return 'acrw1' + index;
}

/**
 * @typedef {Object} SearchResultPartial
 * @property {string} title
 */

/**
 * @typedef {Object} GenerateUrlMeta
 * @property {number} index
 */

/**
 * Used by the `wvui-typeahead-search` component to generate URLs for the search results. Adds a
 * `wprov` paramater to the URL to satisfy the SearchSatisfaction instrumentation.
 *
 * @see getWprovFromResultIndex
 *
 * @param {SearchResultPartial|string} suggestion
 * @param {GenerateUrlMeta} meta
 * @return {string}
 */
function generateUrl( suggestion, meta ) {
	var result = new mw.Uri( wgScript );

	if ( typeof suggestion !== 'string' ) {
		suggestion = suggestion.title;
	}

	result.query.title = 'Special:Search';
	result.query.suggestion = suggestion;
	result.query.wprov = getWprovFromResultIndex( meta.index );

	return result.toString();
}

module.exports = {
	listeners: {
		onFetchEnd: onFetchEnd,
		onSuggestionClick: onSuggestionClick,

		// As of writing (2020/12/08), both the "click-result" and "submit-form" kind of
		// mediawiki.searchSuggestion events result in a "click" SearchSatisfaction event being
		// logged [0]. However, when processing the "submit-form" kind of mediawiki.searchSuggestion
		// event, the SearchSatisfaction instrument will modify the DOM, adding a hidden input
		// element, in order to set the appropriate provenance parameter (see [1] for additional
		// detail).
		//
		// In this implementation of the mediawiki.searchSuggestion protocol, we don't want to
		// trigger the above behavior as we're using Vue.js, which doesn't expect the DOM to be
		// modified underneath it.
		//
		// [0] https://gerrit.wikimedia.org/g/mediawiki/extensions/WikimediaEvents/+/df97aa9c9407507e8c48827666beeab492fd56a8/modules/ext.wikimediaEvents/searchSatisfaction.js#735
		// [1] https://phabricator.wikimedia.org/T257698#6416826
		onSubmit: onSuggestionClick
	},
	getWprovFromResultIndex: getWprovFromResultIndex,
	generateUrl: generateUrl
};
