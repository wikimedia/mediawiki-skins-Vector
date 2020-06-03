/**
 * @typedef {Object} Indicator
 * @prop {string} html of the indicator link.
 * @prop {string} id of the indicator.
 * @prop {string} class of the indicator
 */

/**
 * @typedef {Object} LogoOptions
 * @prop {string} src of logo. Can be relative, absolute or data uri.
 * @prop {string} [alt] text of logo.
 * @prop {number} width of asset
 * @prop {number} height of asset
 */

/**
 * @typedef {Object} ResourceLoaderSkinModuleLogos
 * @prop {string} [icon] e.g. Wikipedia globe
 * @prop {LogoOptions} [wordmark] e.g. Legacy Vector logo
 * @prop {LogoOptions} [tagline] e.g. Legacy Vector logo
 */

/**
 * @typedef {Object} LogoTemplateData
 * @prop {ResourceLoaderSkinModuleLogos} data-logos as configured,
 *  the return value of ResourceLoaderSkinModule::getAvailableLogos.
 * @prop {string} msg-sitetitle alternate text for wordmark
	href the url to navigate to on click.
 * @prop {string} msg-sitesubtitle alternate text for tagline.
 */

/**
 * @typedef {Object} SearchData
 * @prop {string|null} msg-search
 * @prop {string} [html-userlangattributes]
 * @prop {string} form-action URL
 * @prop {string} form-id
 * @prop {string|null} html-input
 * @prop {string|null} page-title the title of the search page
 * @prop {string|null} html-button-search-fallback
 * @prop {string|null} html-button-search
 */

/**
 * @typedef {Object} MenuDefinition
 * @prop {string} id
 * @prop {string} label-id
 * @prop {string} label
 * @prop {string} html-items
 * @prop {string} [html-tooltip]
 * @prop {string} [class] of menu
 * @prop {string} list-classes of the unordered list element inside the menu
 * @prop {string} [html-userlangattributes]
 * @prop {boolean} [is-dropdown]
 * @prop {string} [html-hook-vector-after-toolbox] Deprecated and used by the toolbox portal menu.
 * @prop {string} [html-after-portal] Additional HTML specific to portal menus.
 */
