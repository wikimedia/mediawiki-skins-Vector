import mustache from 'mustache';
import { menuTemplate } from './Menu.stories.data';
import { PERSONAL_MENU_TEMPLATE_DATA, USER_LINKS_LOGGED_IN_TEMPLATE_DATA } from './UserLinks.stories.data';
import { userLinksTemplateLegacy, userLinksTemplate } from './UserLinks.stories.data';

export default {
	title: 'UserLinks'
};

export const legacyLoggedOut = () => mustache.render(
	userLinksTemplateLegacy,
	{
		'data-personal': PERSONAL_MENU_TEMPLATE_DATA.loggedOut
	},
	{
		Menu: menuTemplate
	}
);

export const legacyLoggedInWithEcho = () => mustache.render(
	userLinksTemplateLegacy,
	{
		'data-personal': PERSONAL_MENU_TEMPLATE_DATA.loggedInWithEcho
	},
	{
		Menu: menuTemplate
	}
);

export const legacyLoggedInWithULS = () => mustache.render(
	userLinksTemplateLegacy,
	{
		'data-personal': PERSONAL_MENU_TEMPLATE_DATA.loggedInWithULS
	},
	{
		Menu: menuTemplate
	}
);

export const userLinks = () => mustache.render(
	userLinksTemplate,
	USER_LINKS_LOGGED_IN_TEMPLATE_DATA,
	{
		Menu: menuTemplate
	}
);
