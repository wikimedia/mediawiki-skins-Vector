// Enable Vector features limited to ES6 browse
const
	searchToggle = require( './searchToggle.js' ),
	stickyHeader = require( './stickyHeader.js' ),
	scrollObserver = require( './scrollObserver.js' ),
	AB = require( './AB.js' );

/**
 * @return {void}
 */
const main = () => {
	// Initialize the search toggle for the main header only. The sticky header
	// toggle is initialized after wvui search loads.
	const searchToggleElement = document.querySelector( '.mw-header .search-toggle' );
	if ( searchToggleElement ) {
		searchToggle( searchToggleElement );
	}

	// Get the A/B test config for sticky header if enabled.
	const
		testConfig = AB.getEnabledExperiment(),
		stickyConfig = testConfig &&
			// @ts-ignore
			testConfig.experimentName === stickyHeader.STICKY_HEADER_EXPERIMENT_NAME ?
			testConfig : null,
		// Note that the default test group is set to experience the feature by default.
		// @ts-ignore
		testGroup = stickyConfig ? stickyConfig.group : scrollObserver.FEATURE_TEST_GROUP,
		targetElement = stickyHeader.header;

	// Check for target html, sticky header conditionals, and test group to continue.
	if ( !( targetElement &&
		stickyHeader.isStickyHeaderAllowed() &&
		testGroup !== 'unsampled' )
	) {
		return;
	}

	// Fire the A/B test enrollment hook.
	AB.initAB( testGroup );

	// Set up intersection observer for sticky header functionality and firing scroll event hooks
	// for event logging if AB test is enabled.
	const observer = scrollObserver.initScrollObserver(
		() => {
			scrollObserver.onShowFeature( targetElement, testGroup );
			scrollObserver.logScrollEvent( 'down' );
		},
		() => {
			scrollObserver.onHideFeature( targetElement, testGroup );
			scrollObserver.logScrollEvent( 'up' );
		}

	);

	stickyHeader.initStickyHeader( observer );
};

module.exports = {
	main: main
};
