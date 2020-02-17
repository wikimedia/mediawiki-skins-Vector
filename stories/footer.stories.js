import mustache from 'mustache';
import { FOOTER_TEMPLATE_DATA, footerTemplate } from './footer.stories.data';
import '../resources/skins.vector.styles/footer.less';
import '../.storybook/common.less';

export default {
	title: 'Footer'
};

export const footer = () => mustache.render( footerTemplate, FOOTER_TEMPLATE_DATA );
