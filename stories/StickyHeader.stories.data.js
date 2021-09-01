import template from '!!raw-loader!../includes/templates/StickyHeader.mustache';
import Icon from '!!raw-loader!../includes/templates/Icon.mustache';

const NO_ICON = {
	icon: 'none',
	class: 'sticky-header-icon'
};

const data = {
	title: 'Audre Lorde',
	heading: 'Introduction',
	'primary-action': 'Primary action',
	'is-visible': true,
	'data-icon-start': NO_ICON,
	'data-icon-end': NO_ICON,
	'data-icons': [
		NO_ICON, NO_ICON, NO_ICON, NO_ICON
	]
};

export const STICKY_HEADER_TEMPLATE_PARTIALS = {
	Icon
};

export { template, data };
