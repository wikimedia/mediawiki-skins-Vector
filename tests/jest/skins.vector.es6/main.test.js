const { test } = require( '../../../resources/skins.vector.es6/main.js' );
const {
	STICKY_HEADER_EXPERIMENT_NAME,
	STICKY_HEADER_EDIT_EXPERIMENT_NAME
} = require( '../../../resources/skins.vector.es6/stickyHeader.js' );
describe( 'main.js', () => {
	it( 'getHeadingIntersectionHandler', () => {
		const section = document.createElement( 'div' );
		section.setAttribute( 'class', 'mw-body-content' );
		section.setAttribute( 'id', 'mw-content-text' );
		const heading = document.createElement( 'h2' );
		const headline = document.createElement( 'span' );
		headline.classList.add( 'mw-headline' );
		headline.setAttribute( 'id', 'headline' );
		heading.appendChild( headline );
		section.appendChild(
			heading
		);

		[
			[ section, 'toc-mw-content-text' ],
			[ heading, 'toc-headline' ]
		].forEach( ( testCase ) => {
			const node = /** @type {HTMLElement} */ ( testCase[ 0 ] );
			const fn = jest.fn();
			const handler = test.getHeadingIntersectionHandler( fn );
			handler( node );
			expect( fn ).toHaveBeenCalledWith( testCase[ 1 ] );
		} );
	} );

	it( 'initStickyHeaderABTests', () => {
		const STICKY_HEADER_AB = {
			name: STICKY_HEADER_EXPERIMENT_NAME,
			enabled: true
		};
		const STICKY_HEADER_AB_EDIT = {
			name: STICKY_HEADER_EDIT_EXPERIMENT_NAME,
			enabled: true
		};
		[
			{
				abConfig: STICKY_HEADER_AB_EDIT,
				isEnabled: false,
				isUserInTreatmentBucket: false,
				expectedResult: {
					showStickyHeader: false,
					disableEditIcons: true
				}
			},
			{
				abConfig: STICKY_HEADER_AB_EDIT,
				isEnabled: true,
				isUserInTreatmentBucket: false,
				expectedResult: {
					showStickyHeader: true,
					disableEditIcons: true
				}
			},
			{
				abConfig: STICKY_HEADER_AB_EDIT,
				isEnabled: true,
				isUserInTreatmentBucket: true,
				expectedResult: {
					showStickyHeader: true,
					disableEditIcons: false
				}
			},
			{
				abConfig: STICKY_HEADER_AB,
				isEnabled: false, // sticky header unavailable
				isUserInTreatmentBucket: false, // not in treament bucket
				expectedResult: {
					showStickyHeader: false,
					disableEditIcons: true
				}
			},
			{
				abConfig: STICKY_HEADER_AB,
				isEnabled: true, // sticky header available
				isUserInTreatmentBucket: false, // not in treament bucket
				expectedResult: {
					showStickyHeader: false,
					disableEditIcons: true
				}
			},
			{
				abConfig: STICKY_HEADER_AB,
				isEnabled: false, // sticky header is not available
				isUserInTreatmentBucket: true, // but the user is in the treament bucket
				expectedResult: {
					showStickyHeader: false,
					disableEditIcons: true
				}
			},
			{
				abConfig: STICKY_HEADER_AB,
				isEnabled: true,
				isUserInTreatmentBucket: true,
				expectedResult: {
					showStickyHeader: true,
					disableEditIcons: true
				}
			}
		].forEach( ( { abConfig, isEnabled, isUserInTreatmentBucket, expectedResult } ) => {
			document.documentElement.classList.add( 'vector-sticky-header-enabled' );
			const result = test.initStickyHeaderABTests(
				abConfig,
				isEnabled,
				( experiment ) => ( {
					name: experiment.name,
					isInBucket: () => true,
					isInSample: () => true,
					getBucket: () => 'bucket',
					isInTreatmentBucket: () => {
						return isUserInTreatmentBucket;
					}
				} )
			);
			expect( result ).toMatchObject( expectedResult );
		} );
	} );
} );
