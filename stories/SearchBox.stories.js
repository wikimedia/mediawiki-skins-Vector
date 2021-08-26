import mustache from 'mustache';
import '../resources/skins.vector.styles/SearchBox.less';
import '../resources/skins.vector.styles/layouts/screen.less';
import { searchBoxData, legacySearchBoxData, searchBoxTemplate } from './SearchBox.stories.data';

export default {
	title: 'SearchBox'
};

export const legacySimpleSearch = () => `
	${mustache.render( searchBoxTemplate, legacySearchBoxData )}
`;

export const simpleSearch = () => `
	${mustache.render( searchBoxTemplate, searchBoxData )}
`;
