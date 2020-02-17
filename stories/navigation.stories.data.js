import navTemplate from '!!raw-loader!../includes/templates/Navigation.mustache';
import { PERSONAL_MENU_TEMPLATE_DATA, personalMenuTemplate } from './personalNavigation.stories.data';
import { pageActionsData, namespaceTabsData, vectorTabsTemplate } from './tabs.stories.data';
import { vectorMenuTemplate, moreData, variantsData } from './menu.stories.data';
import { searchBoxData, searchBoxTemplate } from './searchBox.stories.data';
import { SIDEBAR_DATA, SIDEBAR_TEMPLATE_PARTIALS, sidebarTemplate } from './sidebar.stories.data';

export const NAVIGATION_TEMPLATE_PARTIALS = Object.assign( {}, SIDEBAR_TEMPLATE_PARTIALS, {
	SearchBox: searchBoxTemplate,
	Sidebar: sidebarTemplate,
	VectorTabs: vectorTabsTemplate,
	VectorMenu: vectorMenuTemplate,
	PersonalMenu: personalMenuTemplate
} );

export const NAVIGATION_TEMPLATE_DATA = {
	loggedOutWithVariants: {
		'data-personal-menu': PERSONAL_MENU_TEMPLATE_DATA.loggedOut,
		'data-namespace-tabs': namespaceTabsData,
		'data-page-actions': pageActionsData,
		'data-variants': variantsData,
		'data-search-box': searchBoxData,
		'data-sidebar': SIDEBAR_DATA.withPortals,
		'html-navigation-heading': 'Navigation menu',
		'html-logo-attributes': `class="mw-wiki-logo" href="/wiki/Main_Page" title="Visit the main page"`
	},
	loggedInWithMoreActions: {
		'data-personal-menu': PERSONAL_MENU_TEMPLATE_DATA.loggedInWithEcho,
		'data-namespace-tabs': namespaceTabsData,
		'data-page-actions': pageActionsData,
		'data-page-actions-more': moreData,
		'data-search-box': searchBoxData,
		'data-sidebar': SIDEBAR_DATA.withPortals,
		'html-navigation-heading': 'Navigation menu',
		'html-logo-attributes': `class="mw-wiki-logo" href="/wiki/Main_Page" title="Visit the main page"`
	}
};

export { navTemplate };
