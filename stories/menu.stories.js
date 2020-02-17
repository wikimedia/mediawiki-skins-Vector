import mustache from 'mustache';
import '../resources/skins.vector.styles/navigation.less';
import '../.storybook/common.less';
import { vectorMenuTemplate, moreData, variantsData } from './menu.stories.data';

export default {
	title: 'Menu'
};

export const more = () => mustache.render( vectorMenuTemplate, moreData );

export const variants = () => mustache.render( vectorMenuTemplate, variantsData );
