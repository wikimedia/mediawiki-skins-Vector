const ACTIVE_SECTION_CLASS = 'sidebar-toc-list-item-active';
const PARENT_SECTION_CLASS = 'sidebar-toc-level-1';
const LINK_CLASS = 'sidebar-toc-link';
const LIST_ITEM_CLASS = 'sidebar-toc-list-item';

/**
 * Sets an `ACTIVE_SECTION_CLASS` on the element with an id that matches `id`.
 * If the element is not a top level heading (e.g. element with the
 * `PARENT_SECTION_CLASS`), the top level heading will also have the
 * `ACTIVE_SECTION_CLASS`;
 *
 * @callback ActivateSection
 * @param {string} id The id of the element to be activated in the Table of Contents.
 */

/**
 * Called when a list item is clicked.
 *
 * @callback OnSectionClick
 * @param {string} id The id of the clicked list item.
 */

/**
 * @typedef {Object} TableOfContents
 * @property {ActivateSection} activateSection
 */

/**
 * Initializes the sidebar's Table of Contents.
 *
 * @param {Object} props
 * @param {HTMLElement} props.container
 * @param {OnSectionClick} [props.onSectionClick]
 * @return {TableOfContents}
 */
module.exports = function tableOfContents( props ) {
	props = Object.assign( {
		onSectionClick: () => {}
	}, props );

	let /** @type {HTMLElement | undefined} */ activeParentSection;
	let /** @type {HTMLElement | undefined} */ activeChildSection;

	/**
	 * @param {string} id
	 */
	function activateSection( id ) {
		const tocSection = document.getElementById( id );

		if ( !tocSection ) {
			return;
		}

		const parentSection = /** @type {HTMLElement} */ ( tocSection.closest( `.${PARENT_SECTION_CLASS}` ) );

		if ( activeChildSection ) {
			// eslint-disable-next-line mediawiki/class-doc
			activeChildSection.classList.remove( ACTIVE_SECTION_CLASS );
		}
		if ( activeParentSection ) {
			// eslint-disable-next-line mediawiki/class-doc
			activeParentSection.classList.remove( ACTIVE_SECTION_CLASS );
		}

		// eslint-disable-next-line mediawiki/class-doc
		tocSection.classList.add( ACTIVE_SECTION_CLASS );

		if ( parentSection ) {
			// eslint-disable-next-line mediawiki/class-doc
			parentSection.classList.add( ACTIVE_SECTION_CLASS );
		}

		activeChildSection = tocSection;
		activeParentSection = parentSection || undefined;
	}

	function bindClickListener() {
		props.container.addEventListener( 'click', function ( e ) {
			if (
				!( e.target instanceof HTMLElement && e.target.classList.contains( LINK_CLASS ) )
			) {
				return;
			}

			const tocSection =
				/** @type {HTMLElement | null} */ ( e.target.closest( `.${LIST_ITEM_CLASS}` ) );

			if ( tocSection && tocSection.id ) {
				activateSection( tocSection.id );
				// @ts-ignore
				props.onSectionClick( tocSection.id );
			}
		} );
	}

	bindClickListener();

	return {
		activateSection
	};
};
