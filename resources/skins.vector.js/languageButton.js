/**
 * Copies interwiki links to main menu
 *
 * Temporary solution to T287206, can be removed when the new ULS built in Vue.js
 * has been released and contains this
 */
function addInterwikiLinkToMainMenu() {
	// eslint-disable-next-line no-jquery/no-global-selector
	var $editLink = $( '#p-lang-btn .wbc-editpage' ),
		addInterlanguageLink;

	if ( !$editLink.length ) {
		return;
	}

	// @ts-ignore
	addInterlanguageLink = mw.util.addPortletLink(
		'p-tb',
		$editLink.attr( 'href' ) || '#',
		// Original text is "Edit links".
		// Since its taken out of context the title is more descriptive.
		$editLink.attr( 'title' ),
		'wbc-editpage',
		$editLink.attr( 'title' )
	);

	if ( addInterlanguageLink ) {
		addInterlanguageLink.addEventListener( 'click', function ( /** @type {Event} */ e ) {
			e.preventDefault();
			// redirect to the detached and original edit link
			$editLink.trigger( 'click' );
		} );
	}
}

/**
 * Initialize the language button.
 */
module.exports = function () {
	addInterwikiLinkToMainMenu();
};
