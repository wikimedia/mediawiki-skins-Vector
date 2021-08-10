/**
 * Copies interwiki links to sidebar
 *
 * Temporary solution to T287206, can be removed when the new ULS built in Vue.js
 * has been released and contains this
 */
function addInterwikiLinkToSidebar() {
	// eslint-disable-next-line no-jquery/no-global-selector
	var $editLink = $( '#p-lang-btn .wbc-editpage' );
	if ( $editLink.length ) {
		// Use title attribute for link text
		$editLink.text( $editLink.attr( 'title' ) || '' );
		var $li = $( '<li>' )
			// If the Wikibase code runs last, this class is required so it matches the selector @:
			// https://gerrit.wikimedia.org/g/mediawiki/extensions/Wikibase/+/f2e96e1b08fc5ae2e2e92f05d5eda137dc6b1bc8/client/resources/wikibase.client.linkitem.init.js#82
			.addClass( 'wb-langlinks-link' )
			.append( $editLink );
		$li.appendTo( '#p-tb ul' );
	}
}

/**
 * Disable dropdown behaviour for non-JS users.
 *
 * @param {HTMLElement|null} pLangBtn
 * @return {void}
 */
function disableDropdownBehavior( pLangBtn ) {
	if ( !pLangBtn ) {
		return;
	}
	pLangBtn.classList.add( 'vector-menu--hide-dropdown' );
}

/**
 * Checks whether ULS is enabled and if so disables the default
 * drop down behavior of the button.
 */
function disableLanguageDropdown() {
	var ulsModuleStatus = mw.loader.getState( 'ext.uls.interface' ),
		pLangBtnLabel;

	// If module status is defined and not registered we can assume it is in the process of loading
	if ( ulsModuleStatus && ulsModuleStatus !== 'registered' ) {
		// HACK: Ideally knowledge of internal ULS configuration would not be necessary
		// In future this should be wired up to an `mw.hook` event.
		if ( mw.config.get( 'wgULSisCompactLinksEnabled' ) ) {
			disableDropdownBehavior( document.getElementById( 'p-lang-btn' ) );
		}
	} else {
		pLangBtnLabel = document.getElementById( 'p-lang-btn-label' );
		if ( !pLangBtnLabel ) {
			return;
		}

		// Remove .mw-interlanguage-selector to show the dropdown arrow since evidently
		// ULS is not used.
		pLangBtnLabel.classList.remove( 'mw-interlanguage-selector' );
	}
}

/**
 * Initialize the language button.
 */
module.exports = function () {
	disableLanguageDropdown();
	addInterwikiLinkToSidebar();
};
