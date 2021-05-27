/**
 * @param {string} msg
 * @param {number} [height=200]
 * @return {string}
 */
const placeholder = ( msg, height ) => {
	return `<div style="width: 100%; height: ${height || 200}px; margin-bottom: 2px;
		font-size: 12px; padding: 8px; box-sizing: border-box;
		display: flex; background: #eee; align-items: center;justify-content: center;">${msg}</div>`;
};

/**
 * @param {string} html
 * @return {string}
 */
const portletAfter = ( html ) => {
	return `<div class="after-portlet after-portlet-tb">${html}</div>`;
};

const htmlUserLanguageAttributes = `dir="ltr" lang="en-GB"`;

/**
 * @param {string} name of the menu
 * @param {string} htmlItems
 * @param {string} [additionalClassString] to add to the menu
 * @return {MenuDefinition}
 */
function helperMakeMenuData( name, htmlItems, additionalClassString = '' ) {
	let label;
	switch ( name ) {
		case 'personal':
			label = 'Personal tools';
			break;
		default:
			label = 'Menu label';
			break;
	}

	return {
		id: `p-${name}`,
		class: `mw-portlet mw-portlet-${name} vector-menu ${additionalClassString}`,
		label,
		'html-user-language-attributes': htmlUserLanguageAttributes,
		'html-items': htmlItems
	};
}

export { placeholder, htmlUserLanguageAttributes, portletAfter, helperMakeMenuData };
