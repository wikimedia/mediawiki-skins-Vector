/**
 * Appends a query param to the `href` attribute of any clicked anchor element
 * or anchor element that is the parent of a clicked HTMLElement. Links that
 * lead to different origins or have the same pathname and have a hash fragment
 * are ignored.
 *
 * @param {string} key
 * @param {string} value
 * @return {Function} A cleanup function is returned that
 * removes any event listeners that were added.
 */
function linkHijack( key, value ) {
	/**
	 * @param {MouseEvent} e
	 */
	function handleClick( e ) {
		if ( !e.target || !( e.target instanceof HTMLElement ) ) {
			return;
		}

		const anchor = e.target.closest( 'a' );
		if ( !anchor ) {
			return;
		}

		let locationUrl;
		let oldUrl;
		try {
			// eslint-disable-next-line compat/compat
			locationUrl = new URL( location.href );
			// eslint-disable-next-line compat/compat
			oldUrl = new URL( anchor.href );

		} catch ( error ) {
			// A TypeError may be thrown for invalid URLs. In that case, return
			// gracefully.
			return;
		}

		if (
			locationUrl.origin !== oldUrl.origin ||
			( locationUrl.pathname === oldUrl.pathname && oldUrl.hash )
		) {
			// Return early if link leads to host outside the current one or if the
			// url contains a pathname that is the same as the current one and also
			// has a hash fragment (e.g. this occurs with links in the TOC and we
			// don't want to trigger a refresh of the page by appending a query
			// param).
			return;
		}

		// eslint-disable-next-line compat/compat
		const params = new URLSearchParams( oldUrl.search );
		if ( !params.has( key ) ) {
			params.append( key, value );
		}

		// eslint-disable-next-line compat/compat
		const newUrl = new URL( `${oldUrl.origin}${oldUrl.pathname}?${params}${oldUrl.hash}` );
		anchor.setAttribute( 'href', newUrl.toString() );

	}

	document.body.addEventListener( 'click', handleClick );

	return () => {
		document.body.removeEventListener( 'click', handleClick );
	};
}

module.exports = linkHijack;
