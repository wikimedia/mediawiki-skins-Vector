var
	HEADER_SELECTOR = 'header',
	SEARCH_BOX_SELECTOR = '.vector-search-box',
	SEARCH_VISIBLE_CLASS = 'vector-header-search-toggled';

/**
 * Binds event handlers necessary for the searchBox to disappear when the user
 * clicks outside the searchBox.
 *
 * @param {HTMLElement} searchBox
 * @param {HTMLElement} header
 */
function bindSearchBoxHandler( searchBox, header ) {
	/**
	 * @param {Event} ev
	 * @ignore
	 */
	function clickHandler( ev ) {
		if (
			ev.target instanceof HTMLElement &&
			// Check if the click target was a suggestion link. WVUI clears the
			// suggestion elements from the DOM when a suggestion is clicked so we
			// can't test if the suggestion is a child of the searchBox.
			//
			// Note: The .closest API is feature detected in `initSearchToggle`.
			!ev.target.closest( '.wvui-typeahead-suggestion' ) &&
			!searchBox.contains( ev.target )
		) {
			// eslint-disable-next-line mediawiki/class-doc
			header.classList.remove( SEARCH_VISIBLE_CLASS );

			document.removeEventListener( 'click', clickHandler );
		}
	}

	document.addEventListener( 'click', clickHandler );
}

/**
 * Binds event handlers necessary for the searchBox to show when the toggle is
 * clicked.
 *
 * @param {HTMLElement} searchBox
 * @param {HTMLElement} header
 * @param {HTMLElement} searchToggle
 */
function bindToggleClickHandler( searchBox, header, searchToggle ) {
	/**
	 * @param {Event} ev
	 * @ignore
	 */
	function handler( ev ) {
		// The toggle is an anchor element. Prevent the browser from navigating away
		// from the page when clicked.
		ev.preventDefault();

		// eslint-disable-next-line mediawiki/class-doc
		header.classList.add( SEARCH_VISIBLE_CLASS );

		// Defer binding the search box handler until after the event bubbles to the
		// top of the document so that the handler isn't called when the user clicks
		// the search toggle. Event bubbled callbacks execute within the same task
		// in the event loop.
		//
		// Also, defer focusing the input to another task in the event loop. At the time
		// of this writing, Safari 14.0.3 has trouble changing the visibility of the
		// element and focusing the input within the same task.
		setTimeout( function () {
			bindSearchBoxHandler( searchBox, header );

			var searchInput = /** @type {HTMLInputElement|null} */ ( searchBox.querySelector( 'input[type="search"]' ) );

			if ( searchInput ) {
				searchInput.focus();
			}
		} );
	}

	searchToggle.addEventListener( 'click', handler );
}

/**
 * Enables search toggling behavior in a header given a toggle element (e.g.
 * search icon).  When the toggle element is clicked, a class,
 * `SEARCH_VISIBLE_CLASS`, will be applied to a header matching the selector
 * `HEADER_SELECTOR` and the input inside the element, SEARCH_BOX_SELECTOR, will
 * be focused.  This class can be used in CSS to show/hide the necessary
 * elements. When the user clicks outside of SEARCH_BOX_SELECTOR, the class will
 * be removed.
 *
 * @param {HTMLElement|null} searchToggle
 */
module.exports = function initSearchToggle( searchToggle ) {
	// Check if .closest API is available (IE11 does not support it).
	if ( !searchToggle || !searchToggle.closest ) {
		return;
	}

	var header =
	/** @type {HTMLElement|null} */ ( searchToggle.closest( HEADER_SELECTOR ) );

	if ( !header ) {
		return;
	}

	var searchBox =
	/** @type {HTMLElement|null} */ ( header.querySelector( SEARCH_BOX_SELECTOR ) );

	if ( !searchBox ) {
		return;
	}

	bindToggleClickHandler( searchBox, header, searchToggle );
};
