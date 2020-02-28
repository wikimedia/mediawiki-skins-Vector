import mustache from 'mustache';
import { navTemplate, NAVIGATION_TEMPLATE_DATA,
	NAVIGATION_TEMPLATE_PARTIALS } from './navigation.stories.data';
import '../.storybook/common.less';
import '../resources/skins.vector.styles/Navigation.less';

export default {
	title: 'Navigation (Header + Sidebar)'
};

export const navigationLoggedOutWithVariants = () => mustache.render( navTemplate,
	NAVIGATION_TEMPLATE_DATA.loggedOutWithVariants,
	NAVIGATION_TEMPLATE_PARTIALS
);

export const navigationLoggedInWithMore = () => mustache.render( navTemplate,
	NAVIGATION_TEMPLATE_DATA.loggedInWithMoreActions,
	NAVIGATION_TEMPLATE_PARTIALS
);
