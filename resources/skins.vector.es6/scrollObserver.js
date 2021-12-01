const
	FEATURE_VISIBLE_CLASS = 'vector-sticky-header-visible',
	FEATURE_TEST_GROUP = 'stickyHeaderEnabled',
	SCROLL_HOOK = 'vector.page_title_scroll',
	SCROLL_CONTEXT_ABOVE = 'scrolled-above-page-title',
	SCROLL_CONTEXT_BELOW = 'scrolled-below-page-title',
	SCROLL_ACTION = 'scroll-to-top';

/**
 * Determine if user is in test group to experience feature.
 *
 * @param {string} bucket the bucket name the user is assigned
 * @param {string} targetGroup the target test group to experience feature
 * @return {boolean} true if the user should experience feature
 */
function isInTestGroup( bucket, targetGroup ) {
	return bucket === targetGroup;
}

/**
 * Show the feature based on test group.
 *
 * @param {HTMLElement} element target feature
 * @param {string} group A/B test bucket of the user
 */
function onShowFeature( element, group ) {
	if ( isInTestGroup( group, FEATURE_TEST_GROUP ) ) {
		// eslint-disable-next-line mediawiki/class-doc
		element.classList.add( FEATURE_VISIBLE_CLASS );
	}
}

/**
 * Hide the feature based on test group.
 *
 * @param {HTMLElement} element target feature
 * @param {string} group A/B test bucket of the user
 */
function onHideFeature( element, group ) {
	if ( isInTestGroup( group, FEATURE_TEST_GROUP ) ) {
		// eslint-disable-next-line mediawiki/class-doc
		element.classList.remove( FEATURE_VISIBLE_CLASS );
	}
}

/**
 * Fire a hook to be captured by WikimediaEvents for scroll event logging.
 *
 * @param {string} direction the scroll direction
 */
function fireScrollHook( direction ) {
	if ( direction === 'down' ) {
		// @ts-ignore
		mw.hook( SCROLL_HOOK ).fire( { context: SCROLL_CONTEXT_BELOW } );
	} else {
		// @ts-ignore
		mw.hook( SCROLL_HOOK ).fire( {
			context: SCROLL_CONTEXT_ABOVE,
			action: SCROLL_ACTION
		} );
	}
}

/**
 * Create an observer for showing/hiding feature and for firing scroll event hooks.
 *
 * @param {Function} show functionality for when feature is visible
 * @param {Function} hide functionality for when feature is hidden
 * @return {IntersectionObserver}
 */
function initScrollObserver( show, hide ) {
	/* eslint-disable-next-line compat/compat */
	return new IntersectionObserver( function ( entries ) {
		if ( !entries[ 0 ].isIntersecting && entries[ 0 ].boundingClientRect.top < 0 ) {
			// Viewport has crossed the bottom edge of the target element.
			show();
		} else {
			// Viewport is above the bottom edge of the target element.
			hide();
		}
	} );
}

module.exports = {
	initScrollObserver,
	onShowFeature,
	onHideFeature,
	fireScrollHook,
	FEATURE_TEST_GROUP
};
