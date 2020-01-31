import mustache from 'mustache';
import navTemplate from '!!raw-loader!../includes/templates/Navigation.mustache';
import '../.storybook/common.less';
import '../resources/skins.vector.styles/navigation.less';

import { loggedOut, loggedInWithEcho } from './personalNavigation.stories';
import { viewTabs, namespaceTabs } from './tabs.stories';
import { more, variants } from './menu.stories';
import { simpleSearch } from './searchBox.stories';
import { navigationPortal, otherProjects, toolbox, langlinks } from './portal.stories';
import { placeholder } from './utils';

const HOOKINFO = 'Portals can be added, removed or reordered using SidebarBeforeOutput hook';

export default {
	title: 'Navigation (Header + Main Menu)'
};

export const navigationLoggedOutWithVariants = () => mustache.render( navTemplate,
	{
		'html-personalmenu': loggedOut(),
		'html-navigation-left-tabs': namespaceTabs() + variants(),
		'html-navigation-right-tabs': `${viewTabs()} ${simpleSearch()}`,
		'html-portals': `${navigationPortal().innerHTML}
			${toolbox().innerHTML}
			${otherProjects().innerHTML}
			${langlinks().innerHTML}
			${placeholder( HOOKINFO, 60 )}
`,
		'html-navigation-heading': 'Navigation menu',
		'html-logo-attributes': `class="mw-wiki-logo" href="/wiki/Main_Page" title="Visit the main page"`
	}
);

export const navigationLoggedInWithMore = () => mustache.render( navTemplate,
	{
		'html-personalmenu': loggedInWithEcho(),
		'html-navigation-left-tabs': namespaceTabs(),
		'html-navigation-right-tabs': `${viewTabs()} ${more()} ${simpleSearch()}`,
		'html-portals': `${navigationPortal().innerHTML}
			${toolbox().innerHTML}
			${otherProjects().innerHTML}
			${langlinks().innerHTML}
			${placeholder( HOOKINFO, 60 )}
`,
		'html-navigation-heading': 'Navigation menu',
		'html-logo-attributes': `class="mw-wiki-logo" href="/wiki/Main_Page" title="Visit the main page"`
	}
);
