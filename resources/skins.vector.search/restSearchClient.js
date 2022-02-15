/** @module restSearchClient */

/**
 * @typedef {Object} AbortableFetch
 * @property {Promise<any>} fetch
 * @property {Function} abort
 */

/**
 * @typedef {Object} NullableAbortController
 * @property {AbortSignal | undefined} signal
 * @property {Function} abort
 */
const nullAbortController = {
	signal: undefined,
	abort: () => {} // Do nothing (no-op)
};

/**
 * A wrapper which combines native fetch() in browsers and the following json() call.
 *
 * @param {string} resource
 * @param {RequestInit} [init]
 * @return {AbortableFetch}
 */
function fetchJson( resource, init ) {
	// As of 2020, browser support for AbortController is limited:
	// https://caniuse.com/abortcontroller
	// so replacing it with no-op if it doesn't exist.
	/* eslint-disable compat/compat */
	const controller = window.AbortController ?
		new AbortController() :
		nullAbortController;
	/* eslint-enable compat/compat */

	const getJson = fetch( resource, $.extend( init, {
		signal: controller.signal
	} ) ).then( ( response ) => {
		if ( !response.ok ) {
			return Promise.reject(
				'Network request failed with HTTP code ' + response.status
			);
		}
		return response.json();
	} );

	return {
		fetch: getJson,
		abort: () => {
			controller.abort();
		}
	};
}

/**
 * @typedef {Object} RestResponse
 * @property {RestResult[]} pages
 */

/**
 * @typedef {Object} RestResult
 * @property {number} id
 * @property {string} key
 * @property {string} title
 * @property {string} [description]
 * @property {RestThumbnail | null} [thumbnail]
 *
 */

/**
 * @typedef {Object} RestThumbnail
 * @property {string} url
 * @property {number | null} [width]
 * @property {number | null} [height]
 */

/**
 * @typedef {Object} SearchResponse
 * @property {string} query
 * @property {SearchResult[]} results
 */

/**
 * @typedef {Object} SearchResult
 * @property {number} id
 * @property {string} key
 * @property {string} title
 * @property {string} [description]
 * @property {SearchResultThumbnail} [thumbnail]
 */

/**
 * @typedef {Object} SearchResultThumbnail
 * @property {string} url
 * @property {number} [width]
 * @property {number} [height]
 */

/**
 * Nullish coalescing operator (??) helper
 *
 * @param {any} a
 * @param {any} b
 * @return {any}
 */
function nullish( a, b ) {
	return ( a !== null && a !== undefined ) ? a : b;
}

/**
 * @param {string} query
 * @param {RestResponse} restResponse
 * @return {SearchResponse}
 */
function adaptApiResponse( query, restResponse ) {
	return {
		query,
		results: restResponse.pages.map( ( page ) => {
			const thumbnail = page.thumbnail;
			return {
				id: page.id,
				key: page.key,
				title: page.title,
				description: page.description,
				thumbnail: thumbnail ? {
					url: thumbnail.url,
					width: nullish( thumbnail.width, undefined ),
					height: nullish( thumbnail.height, undefined )
				} : undefined
			};
		} )
	};
}

/**
 * @typedef {Object} AbortableSearchFetch
 * @property {Promise<SearchResponse>} fetch
 * @property {Function} abort
 */

/**
 * @callback fetchByTitle
 * @param {string} query The search term.
 * @param {string} domain The base URL for the wiki without protocol. Example: 'sr.wikipedia.org'.
 * @param {number} [limit] Maximum number of results.
 * @return {AbortableSearchFetch}
 */

/**
 * @typedef {Object} SearchClient
 * @property {fetchByTitle} fetchByTitle
 */

/**
 * @param {MwMap} config
 * @return {SearchClient}
 */
function restSearchClient( config ) {
	const customClient = config.get( 'wgVectorSearchClient' );
	return customClient || {
		/**
		 * @type {fetchByTitle}
		 */
		fetchByTitle: ( q, domain, limit = 10 ) => {
			const params = { q, limit };
			const url = '//' + domain + config.get( 'wgScriptPath' ) + '/rest.php/v1/search/title?' + $.param( params );
			const result = fetchJson( url, {
				headers: {
					accept: 'application/json'
				}
			} );
			const searchResponsePromise = result.fetch
				.then( ( /** @type {RestResponse} */ res ) => {
					return adaptApiResponse( q, res );
				} );
			return {
				abort: result.abort,
				fetch: searchResponsePromise
			};
		}
	};
}

module.exports = restSearchClient;
