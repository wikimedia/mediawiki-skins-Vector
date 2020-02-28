import mustache from 'mustache';
import { personalMenuTemplate, PERSONAL_MENU_TEMPLATE_DATA } from './PersonalMenu.stories.data';
import '../resources/skins.vector.styles/PersonalMenu.less';
import '../.storybook/common.less';

export default {
	title: 'Personal Menu'
};

export const loggedOut = () => mustache.render( personalMenuTemplate,
	PERSONAL_MENU_TEMPLATE_DATA.loggedOut );

export const loggedInWithEcho = () => mustache.render( personalMenuTemplate,
	PERSONAL_MENU_TEMPLATE_DATA.loggedInWithEcho );

export const loggedInWithULS = () => mustache.render( personalMenuTemplate,
	PERSONAL_MENU_TEMPLATE_DATA.loggedInWithULS );
