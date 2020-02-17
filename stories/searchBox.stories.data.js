import searchBoxTemplate from '!!raw-loader!../includes/templates/SearchBox.mustache';
import { htmluserlangattributes } from './utils';

export { searchBoxTemplate };

export const searchBoxData = {
	searchActionURL: '/w/index.php',
	searchHeaderAttrsHTML: htmluserlangattributes,
	searchInputLabel: 'Search',
	searchDivID: 'simpleSearch',
	searchInputHTML: '<input type="search" name="search" placeholder="Search Wikipedia" title="Search Wikipedia [⌃⌥f]" accesskey="f" id="searchInput" autocomplete="off">',
	titleHTML: '<input type="hidden" value="Special:Search" name="title">',
	fallbackSearchButtonHTML: '<input type="submit" name="fulltext" value="Search" title="Search pages for this text" id="mw-searchButton" class="searchButton mw-fallbackSearchButton"/>',
	searchButtonHTML: '<input type="submit" name="go" value="Go" title="Go to a page with this exact name if it exists" id="searchButton" class="searchButton">'
};
