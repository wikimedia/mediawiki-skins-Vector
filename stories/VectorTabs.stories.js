import mustache from 'mustache';
import { namespaceTabsData, pageActionsData, vectorTabsTemplate } from './VectorTabs.stories.data';
import '../resources/skins.vector.styles/VectorTabs.less';
import '../.storybook/common.less';

export default {
	title: 'Tabs'
};

export const pageActionTabs = () => mustache.render( vectorTabsTemplate, pageActionsData );

export const namespaceTabs = () => mustache.render( vectorTabsTemplate, namespaceTabsData );
