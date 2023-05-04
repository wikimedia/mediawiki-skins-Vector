module.exports = function () {
	mw.hook( 'wikipage.watchlistChange' ).add(
		function ( /** @type {boolean} */ isWatched, /** @type {string} */ expiry ) {
			const watchIcon = document.querySelectorAll( '#ca-watch .mw-ui-icon, #ca-unwatch .mw-ui-icon' )[ 0 ];
			if ( !watchIcon ) {
				return;
			}

			watchIcon.classList.remove(
				// Vector attaches two icon classes to the element.
				// Remove the mw-ui-icon one rather than managing both.
				'mw-ui-icon-star',
				'mw-ui-icon-unStar',
				'mw-ui-icon-wikimedia-unStar',
				'mw-ui-icon-wikimedia-star',
				'mw-ui-icon-wikimedia-halfStar'
			);

			if ( isWatched ) {
				if ( expiry === 'infinity' ) {
					watchIcon.classList.add( 'mw-ui-icon-wikimedia-unStar' );
				} else {
					watchIcon.classList.add( 'mw-ui-icon-wikimedia-halfStar' );
				}
			} else {
				watchIcon.classList.add( 'mw-ui-icon-wikimedia-star' );
			}
		}
	);
};
