import mustache from 'mustache';
import '../resources/skins.vector.styles/SearchBox.less';
import '../.storybook/common.less';
import { searchBoxData, searchBoxTemplate } from './searchBox.stories.data';

export default {
	title: 'Search'
};

export const simpleSearch = () => mustache.render( searchBoxTemplate, searchBoxData );
