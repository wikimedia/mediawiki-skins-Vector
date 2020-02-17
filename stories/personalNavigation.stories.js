import mustache from 'mustache';
import { personalMenuTemplate, PERSONAL_MENU_TEMPLATE_DATA } from './personalNavigation.stories.data';
import '../resources/skins.vector.styles/personalNavigation.less';
import '../.storybook/common.less';

export default {
	title: 'Personal Navigation'
};

export const loggedOut = () => mustache.render( personalMenuTemplate,
	PERSONAL_MENU_TEMPLATE_DATA.loggedOut );

export const loggedInWithEcho = () => mustache.render( personalMenuTemplate,
	PERSONAL_MENU_TEMPLATE_DATA.loggedInWithEcho );

export const loggedInWithULS = () => mustache.render( personalMenuTemplate,
	PERSONAL_MENU_TEMPLATE_DATA.loggedInWithULS );
