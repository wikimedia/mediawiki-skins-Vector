import mustache from 'mustache';
import searchBox from '!!raw-loader!../includes/templates/SearchBox.mustache';
import '../resources/skins.vector.styles/search.less';
import '../.storybook/common.less';

export default {
	title: 'Search'
};

export const simpleSearch = () => mustache.render( searchBox, {
	searchActionURL: '/w/index.php',
	searchHeaderAttrsHTML: 'dir="ltr" lang="en-GB"',
	searchInputLabel: 'Search',
	searchDivID: 'simpleSearch',
	searchInputHTML: '<input type="search" name="search" placeholder="Search Wikipedia" title="Search Wikipedia [⌃⌥f]" accesskey="f" id="searchInput" autocomplete="off">',
	titleHTML: '<input type="hidden" value="Special:Search" name="title">',
	fallbackSearchButtonHTML: '<input type="submit" name="fulltext" value="Search" title="Search pages for this text" id="mw-searchButton" class="searchButton mw-fallbackSearchButton"/>',
	searchButtonHTML: '<input type="submit" name="go" value="Go" title="Go to a page with this exact name if it exists" id="searchButton" class="searchButton">'
} );
