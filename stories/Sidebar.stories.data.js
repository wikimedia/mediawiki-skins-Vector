import sidebarTemplate from '!!raw-loader!../includes/templates/Sidebar.mustache';
import portalTemplate from '!!raw-loader!../includes/templates/Portal.mustache';
import { PORTALS } from './Portal.stories.data';

const HTML_LOGO_ATTRIBUTES = `class="mw-wiki-logo" href="/wiki/Main_Page" title="Visit the main page"`;
const SIDEBAR_BEFORE_OUTPUT_HOOKINFO = `Beware: Portals can be added, removed or reordered using
SidebarBeforeOutput hook as in this example.`;

export { sidebarTemplate };

export const SIDEBAR_TEMPLATE_PARTIALS = {
	Portal: portalTemplate
};

export const SIDEBAR_DATA = {
	withNoPortals: {
		'array-portals-rest': [],
		'html-logo-attributes': HTML_LOGO_ATTRIBUTES
	},
	withPortalsAndOptOut: {
		'array-portals-first': PORTALS.navigation,
		'data-emphasized-sidebar-action': {
			href: '#',
			text: 'Switch to old look'
		},
		'array-portals-rest': [
			PORTALS.toolbox,
			PORTALS.otherProjects,
			PORTALS.langlinks
		],
		'html-logo-attributes': HTML_LOGO_ATTRIBUTES
	},
	withPortals: {
		'array-portals-first': PORTALS.navigation,
		'array-portals-rest': [
			PORTALS.toolbox,
			PORTALS.otherProjects,
			PORTALS.langlinks
		],
		'html-logo-attributes': HTML_LOGO_ATTRIBUTES
	},
	thirdParty: {
		'array-portals-rest': [
			PORTALS.toolbox,
			PORTALS.navigation,
			{
				'html-portal-content': SIDEBAR_BEFORE_OUTPUT_HOOKINFO
			}
		],
		'html-logo-attributes': HTML_LOGO_ATTRIBUTES
	}
};
