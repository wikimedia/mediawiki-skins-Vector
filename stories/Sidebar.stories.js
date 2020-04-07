import mustache from 'mustache';
import '../.storybook/common.less';
import '../resources/skins.vector.styles/Sidebar.less';
import '../resources/skins.vector.styles/SidebarLogo.less';
import '../resources/skins.vector.styles/MenuPortal.less';
import { sidebarTemplate, SIDEBAR_DATA, SIDEBAR_TEMPLATE_PARTIALS } from './Sidebar.stories.data';

export default {
	title: 'Sidebar'
};

export const sidebarWithNoPortals = () => mustache.render(
	sidebarTemplate, SIDEBAR_DATA.withNoPortals, SIDEBAR_TEMPLATE_PARTIALS
);

export const sidebarWithoutLogo = () => mustache.render(
	sidebarTemplate, SIDEBAR_DATA.withoutLogo, SIDEBAR_TEMPLATE_PARTIALS
);

export const sidebarWithPortals = () => mustache.render(
	sidebarTemplate, SIDEBAR_DATA.withPortals, SIDEBAR_TEMPLATE_PARTIALS
);

export const sidebarThirdParty = () => mustache.render(
	sidebarTemplate, SIDEBAR_DATA.thirdParty, SIDEBAR_TEMPLATE_PARTIALS
);
