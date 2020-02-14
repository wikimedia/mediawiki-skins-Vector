import mustache from 'mustache';
import sidebarTemplate from '!!raw-loader!../includes/templates/Sidebar.mustache';
import portalTemplate from '!!raw-loader!../includes/templates/Portal.mustache';
import { PORTALS } from './portal.stories.data';
import '../.storybook/common.less';
import '../resources/skins.vector.styles/navigation.less';
const HTML_LOGO_ATTRIBUTES = `class="mw-wiki-logo" href="/wiki/Main_Page" title="Visit the main page"`;
const SIDEBAR_BEFORE_OUTPUT_HOOKINFO = `Beware: Portals can be added, removed or reordered using
SidebarBeforeOutput hook as in this example.`;

export default {
	title: 'Sidebar'
};

export const sidebarWithNoPortals = () => mustache.render( sidebarTemplate,
	{
		'array-portals': [],
		'html-logo-attributes': HTML_LOGO_ATTRIBUTES
	}
);

export const sidebarWithPortals = () => mustache.render( sidebarTemplate,
	{
		'array-portals': [
			PORTALS.navigation,
			PORTALS.toolbox,
			PORTALS.otherProjects,
			PORTALS.langlinks
		],
		'html-logo-attributes': HTML_LOGO_ATTRIBUTES
	},
	{
		Portal: portalTemplate
	}
);

export const sidebarThirdParty = () => mustache.render( sidebarTemplate,
	{
		'array-portals': [
			PORTALS.toolbox,
			PORTALS.navigation,
			{
				'html-portal-content': SIDEBAR_BEFORE_OUTPUT_HOOKINFO
			}
		],
		'html-logo-attributes': HTML_LOGO_ATTRIBUTES
	},
	{
		Portal: portalTemplate
	}
);
