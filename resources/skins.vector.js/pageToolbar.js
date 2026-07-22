const PAGE_TOOLBAR_CLASS = 'vector-page-toolbar-container',
	PAGE_TOOLBAR_SELECTOR = '.' + PAGE_TOOLBAR_CLASS,
	LEFT_NAV_SELECTOR = '#left-navigation',
	RIGHT_NAV_SELECTOR = '#right-navigation';

/**
 * Check if there's enough space in the page toolbar for all nav items.
 * If not, add a class to expand the toolbar to two lines.
 *
 * @param {Element} pageToolbar The entire page toolbar
 * @param {Element} leftNav The left navigation (namespaces)
 * @param {Element} rightNav The right navigation (actions, tools, etc.)
 */
function checkToolbarSpace( pageToolbar, leftNav, rightNav ) {
	// Temporarily display page toolbar on a single line.
	pageToolbar.classList.remove( PAGE_TOOLBAR_CLASS + '--expand' );

	// Get the width of the one-line page toolbar.
	const minWidthSingleLine = pageToolbar.clientWidth;

	// If that's smaller than the combined width of the navs, expand to 2 lines.
	if ( minWidthSingleLine < leftNav.clientWidth + rightNav.clientWidth ) {
		pageToolbar.classList.add( PAGE_TOOLBAR_CLASS + '--expand' );
	}
}

function init() {
	const pageToolbar = document.querySelector( PAGE_TOOLBAR_SELECTOR );
	const leftNav = document.querySelector( LEFT_NAV_SELECTOR );
	const rightNav = document.querySelector( RIGHT_NAV_SELECTOR );

	if ( !pageToolbar || !leftNav || !rightNav ) {
		return;
	}

	window.addEventListener( 'resize', mw.util.debounce( () => {
		checkToolbarSpace( pageToolbar, leftNav, rightNav );
	}, 10 ) );
}

module.exports = init;
