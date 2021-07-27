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
		var $li = $( '<li>' ).append( $editLink );
		$li.appendTo( '#p-tb ul' );
	}
}

/**
 * Checks whether ULS is enabled and if so disables the default
 * drop down behavior of the button.
 */
function disableLanguageDropdown() {
	var ulsModuleStatus = mw.loader.getState( 'ext.uls.interface' ),
		pLangBtnLabel;

	if ( ulsModuleStatus && ulsModuleStatus !== 'registered' ) {
		mw.loader.using( 'ext.uls.interface' ).then( function () {
			var pLangBtn = document.getElementById( 'p-lang-btn' );
			if ( !pLangBtn ) {
				return;
			}
			if ( !pLangBtn.querySelectorAll( '.mw-interlanguage-selector' ).length ) {
				// The ext.uls.interface module removed the selector,
				// because the feature is disabled. Do nothing.
				return;
			}
			pLangBtn.classList.add( 'vector-menu--hide-dropdown' );
		} );
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
