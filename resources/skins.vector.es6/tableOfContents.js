/** @module TableOfContents */

const SECTION_CLASS = 'sidebar-toc-list-item';
const ACTIVE_SECTION_CLASS = 'sidebar-toc-list-item-active';
const EXPANDED_SECTION_CLASS = 'sidebar-toc-list-item-expanded';
const PARENT_SECTION_CLASS = 'sidebar-toc-level-1';
const LINK_CLASS = 'sidebar-toc-link';
const TOGGLE_CLASS = 'sidebar-toc-toggle';

/**
 * @callback onHeadingClick
 * @param {string} id The id of the clicked list item.
 */

/**
 * @callback onToggleClick
 * @param {string} id The id of the list item corresponding to the arrow.
 */

/**
 * @typedef {Object} TableOfContentsProps
 * @property {HTMLElement} container The container element for the table of contents.
 * @property {onHeadingClick} onHeadingClick Called when an arrow is clicked.
 * @property {onToggleClick} onToggleClick Called when a list item is clicked.
 */

/**
 * Initializes the sidebar's Table of Contents.
 *
 * @param {TableOfContentsProps} props
 * @return {TableOfContents}
 */
module.exports = function tableOfContents( props ) {
	let /** @type {HTMLElement | undefined} */ activeTopSection;
	let /** @type {HTMLElement | undefined} */ activeSubSection;
	let /** @type {Array<HTMLElement>} */ expandedSections;

	/**
	 * @typedef {Object} activeSectionIds
	 * @property {string|undefined} parent - The active  top level section ID
	 * @property {string|undefined} child - The active subsection ID
	 */

	/**
	 * Get the ids of the active sections.
	 *
	 * @return {activeSectionIds}
	 */
	function getActiveSectionIds() {
		return {
			parent: ( activeTopSection ) ? activeTopSection.id : undefined,
			child: ( activeSubSection ) ? activeSubSection.id : undefined
		};
	}

	/**
	 * Does the user prefer reduced motion?
	 *
	 * @return {boolean}
	 */
	const prefersReducedMotion = () => {
		return window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	};

	/**
	 * Sets an `ACTIVE_SECTION_CLASS` on the element with an id that matches `id`.
	 * If the element is not a top level heading (e.g. element with the
	 * `PARENT_SECTION_CLASS`), the top level heading will also have the
	 * `ACTIVE_SECTION_CLASS`;
	 *
	 * @param {string} id The id of the element to be activated in the Table of Contents.
	 */
	function activateSection( id ) {
		const selectedTocSection = document.getElementById( id );
		const {
			parent: previousActiveTopId,
			child: previousActiveSubSectionId
		} = getActiveSectionIds();

		if (
			!selectedTocSection ||
			( previousActiveTopId === id ) ||
			( previousActiveSubSectionId === id )
		) {
			return;
		}

		const topSection = /** @type {HTMLElement} */ ( selectedTocSection.closest( `.${PARENT_SECTION_CLASS}` ) );

		if ( selectedTocSection === topSection ) {
			activeTopSection = topSection;
			activeTopSection.classList.add( ACTIVE_SECTION_CLASS );
		} else {
			activeTopSection = topSection;
			activeSubSection = selectedTocSection;
			activeTopSection.classList.add( ACTIVE_SECTION_CLASS );
			activeSubSection.classList.add( ACTIVE_SECTION_CLASS );
		}
	}

	/**
	 * Removes the `ACTIVE_SECTION_CLASS` from all ToC sections.
	 *
	 */
	function deactivateSections() {
		if ( activeSubSection ) {
			activeSubSection.classList.remove( ACTIVE_SECTION_CLASS );
			activeSubSection = undefined;
		}
		if ( activeTopSection ) {
			activeTopSection.classList.remove( ACTIVE_SECTION_CLASS );
			activeTopSection = undefined;
		}
	}

	/**
	 * Scroll active section into view if necessary
	 *
	 * @param {string} id The id of the element to be scrolled to in the Table of Contents.
	 */
	function scrollToActiveSection( id ) {
		const section = document.getElementById( id );
		if ( !section ) {
			return;
		}

		// Get currently visible active link
		let link = section.firstElementChild;
		// @ts-ignore
		if ( link && !link.offsetParent ) {
			// If active link is a hidden subsection, use active parent link
			const { parent: activeTopId } = getActiveSectionIds();
			const parentSection = document.getElementById( activeTopId || '' );
			if ( parentSection ) {
				link = parentSection.firstElementChild;
			} else {
				link = null;
			}
		}

		const isContainerScrollable = props.container.scrollHeight > props.container.clientHeight;
		if ( link && isContainerScrollable ) {
			const containerRect = props.container.getBoundingClientRect();
			const linkRect = link.getBoundingClientRect();

			// Pixels above or below the TOC where we start scrolling the active section into view
			const hiddenThreshold = 100;
			const midpoint = ( containerRect.bottom - containerRect.top ) / 2;
			const linkHiddenTopValue = containerRect.top - linkRect.top;
			// Because the bottom of the TOC can extend below the viewport,
			// min() is used to find the value where the active section first becomes hidden
			const linkHiddenBottomValue = linkRect.bottom -
				Math.min( containerRect.bottom, window.innerHeight );

			// Respect 'prefers-reduced-motion' user preference
			const scrollBehavior = prefersReducedMotion() ? 'smooth' : undefined;

			// Manually increment and decrement TOC scroll rather than using scrollToView
			// in order to account for threshold
			if ( linkHiddenTopValue + hiddenThreshold > 0 ) {
				props.container.scrollTo( {
					top: props.container.scrollTop - linkHiddenTopValue - midpoint,
					behavior: scrollBehavior
				} );
			}
			if ( linkHiddenBottomValue + hiddenThreshold > 0 ) {
				props.container.scrollTo( {
					top: props.container.scrollTop + linkHiddenBottomValue + midpoint,
					behavior: scrollBehavior
				} );
			}
		}
	}

	/**
	 * Adds the `EXPANDED_SECTION_CLASS` CSS class name
	 * to a top level heading in the ToC.
	 *
	 * @param {string} id
	 */
	function expandSection( id ) {
		const tocSection = document.getElementById( id );

		if ( !tocSection ) {
			return;
		}

		const parentSection = /** @type {HTMLElement} */ ( tocSection.closest( `.${PARENT_SECTION_CLASS}` ) );
		const toggle = tocSection.querySelector( `.${TOGGLE_CLASS}` );

		if ( parentSection && toggle && expandedSections.indexOf( parentSection ) < 0 ) {
			toggle.setAttribute( 'aria-expanded', 'true' );
			parentSection.classList.add( EXPANDED_SECTION_CLASS );
			expandedSections.push( parentSection );
		}
	}

	/**
	 * Get the IDs of expanded sections.
	 *
	 * @return {Array<string>}
	 */
	function getExpandedSectionIds() {
		return expandedSections.map( ( s ) => s.id );
	}

	/**
	 *
	 * @param {string} id
	 */
	function changeActiveSection( id ) {

		const { parent: activeParentId, child: activeChildId } = getActiveSectionIds();

		if ( id === activeParentId && id === activeChildId ) {
			return;
		} else {
			deactivateSections();
			activateSection( id );
			scrollToActiveSection( id );
		}
	}

	/**
	 * @param {string} id
	 * @return {boolean}
	 */
	function isTopLevelSection( id ) {
		const section = document.getElementById( id );
		return !!section && section.classList.contains( PARENT_SECTION_CLASS );
	}

	/**
	 * Removes all `EXPANDED_SECTION_CLASS` CSS class names
	 * from the top level sections in the ToC.
	 *
	 * @param {Array<string>} [selectedIds]
	 */
	function collapseSections( selectedIds ) {
		const sectionIdsToCollapse = selectedIds || getExpandedSectionIds();
		expandedSections = expandedSections.filter( function ( section ) {
			const isSelected = sectionIdsToCollapse.indexOf( section.id ) > -1;
			const toggle = isSelected ? section.getElementsByClassName( TOGGLE_CLASS ) : undefined;
			if ( isSelected && toggle && toggle.length > 0 ) {
				toggle[ 0 ].setAttribute( 'aria-expanded', 'false' );
				section.classList.remove( EXPANDED_SECTION_CLASS );
				return false;
			}
			return true;
		} );
	}

	/**
	 * @param {string} id
	 */
	function toggleExpandSection( id ) {
		const expandedSectionIds = getExpandedSectionIds();
		const indexOfExpandedSectionId = expandedSectionIds.indexOf( id );
		if ( isTopLevelSection( id ) ) {
			if ( indexOfExpandedSectionId >= 0 ) {
				collapseSections( [ id ] );
			} else {
				expandSection( id );
			}
		}
	}

	/**
	 * Set aria-expanded attribute for all toggle buttons.
	 */
	function initializeExpandedStatus() {
		const parentSections = props.container.querySelectorAll( `.${PARENT_SECTION_CLASS}` );
		parentSections.forEach( ( section ) => {
			const expanded = section.classList.contains( EXPANDED_SECTION_CLASS );
			const toggle = section.querySelector( `.${TOGGLE_CLASS}` );
			if ( toggle ) {
				toggle.setAttribute( 'aria-expanded', expanded.toString() );
			}
		} );
	}

	/**
	 * Bind event listeners for clicking on section headings and toggle buttons.
	 */
	function bindClickListener() {
		props.container.addEventListener( 'click', function ( e ) {
			if (
				!( e.target instanceof HTMLElement )
			) {
				return;
			}

			const tocSection =
				/** @type {HTMLElement | null} */ ( e.target.closest( `.${SECTION_CLASS}` ) );

			if ( tocSection && tocSection.id ) {
				// In case section link contains HTML,
				// test if click occurs on any child elements.
				if ( e.target.closest( `.${LINK_CLASS}` ) ) {
					props.onHeadingClick( tocSection.id );
				}
				// Toggle button does not contain child elements,
				// so classList check will suffice.
				if ( e.target.classList.contains( TOGGLE_CLASS ) ) {
					props.onToggleClick( tocSection.id );
				}
			}

		} );
	}

	/**
	 * Binds event listeners and sets the default state of the component.
	 */
	function initialize() {
		// Sync component state to the default rendered state of the table of contents.
		expandedSections = Array.from(
			props.container.querySelectorAll( `.${EXPANDED_SECTION_CLASS}` )
		);

		// Initialize toggle buttons aria-expanded attribute.
		initializeExpandedStatus();

		// Bind event listeners.
		bindClickListener();
	}

	initialize();

	/**
	 * @typedef {Object} TableOfContents
	 * @property {changeActiveSection} changeActiveSection
	 * @property {expandSection} expandSection
	 * @property {toggleExpandSection} toggleExpandSection
	 * @property {string} ACTIVE_SECTION_CLASS
	 * @property {string} EXPANDED_SECTION_CLASS
	 * @property {string} LINK_CLASS
	 * @property {string} TOGGLE_CLASS
	 */
	return {
		expandSection,
		changeActiveSection,
		toggleExpandSection,
		ACTIVE_SECTION_CLASS,
		EXPANDED_SECTION_CLASS,
		LINK_CLASS,
		TOGGLE_CLASS
	};
};
