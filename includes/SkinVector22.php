<?php

namespace MediaWiki\Skins\Vector;

use MediaWiki\MediaWikiServices;
use MediaWiki\Skins\Vector\Components\VectorComponentMainMenu;
use MediaWiki\Skins\Vector\Components\VectorComponentPinnableHeader;
use MediaWiki\Skins\Vector\Components\VectorComponentSearchBox;

/**
 * @ingroup Skins
 * @package Vector
 * @internal
 */
class SkinVector22 extends SkinVector {
	private const TOC_AB_TEST_NAME = 'skin-vector-toc-experiment';
	private const STICKY_HEADER_ENABLED_CLASS = 'vector-sticky-header-enabled';

	/**
	 * Updates the constructor to conditionally disable table of contents in article
	 * body. Note, the constructor can only check feature flags that do not vary on
	 * whether the user is logged in e.g. features with the 'default' key set.
	 * @inheritDoc
	 */
	public function __construct( array $options ) {
		if ( !$this->isTOCABTestEnabled() ) {
			$options['toc'] = !$this->isTableOfContentsVisibleInSidebar();
		} else {
			$options['styles'][] = 'skins.vector.AB.styles';
		}

		parent::__construct( $options );
	}

	/**
	 * Show the ULS button if it's modern Vector, languages in header is enabled,
	 * and the ULS extension is enabled. Hide it otherwise.
	 * There is no point in showing the language button if ULS extension is unavailable
	 * as there is no ways to add languages without it.
	 * @return bool
	 */
	protected function shouldHideLanguages(): bool {
		return !$this->isLanguagesInContent() || !$this->isULSExtensionEnabled();
	}

	/**
	 * Determines if the language switching alert box should be in the sidebar.
	 *
	 * @return bool
	 */
	private function shouldLanguageAlertBeInSidebar(): bool {
		$featureManager = VectorServices::getFeatureManager();
		$isMainPage = $this->getTitle() ? $this->getTitle()->isMainPage() : false;
		$shouldShowOnMainPage = $isMainPage && !empty( $this->getLanguagesCached() ) &&
			$featureManager->isFeatureEnabled( Constants::FEATURE_LANGUAGE_IN_MAIN_PAGE_HEADER );
		return ( $this->isLanguagesInContentAt( 'top' ) && !$isMainPage && !$this->shouldHideLanguages() &&
			$featureManager->isFeatureEnabled( Constants::FEATURE_LANGUAGE_ALERT_IN_SIDEBAR ) ) ||
			$shouldShowOnMainPage;
	}

	/**
	 * @internal
	 * @return bool
	 */
	public function isTOCABTestEnabled(): bool {
		$experimentConfig = $this->getConfig()->get( Constants::CONFIG_WEB_AB_TEST_ENROLLMENT );

		return $experimentConfig['name'] === self::TOC_AB_TEST_NAME &&
			$experimentConfig['enabled'];
	}

	/**
	 * Check whether the user is bucketed in the treatment group for TOC.
	 *
	 * @return bool
	 */
	public function isUserInTocTreatmentBucket(): bool {
		$featureManager = VectorServices::getFeatureManager();
		return !$featureManager->isFeatureEnabled( Constants::FEATURE_TABLE_OF_CONTENTS_AB_TEST );
	}

	/**
	 * Determines if the Table of Contents should be visible.
	 * TOC is visible on main namespaces except for the Main Page.
	 *
	 * @internal
	 * @return bool
	 */
	public function isTableOfContentsVisibleInSidebar(): bool {
		$title = $this->getTitle();

		if (
			!$title ||
			$title->isMainPage()
		) {
			return false;
		}

		if ( $this->isTOCABTestEnabled() ) {
			return $title->getArticleID() !== 0;
		}

		return true;
	}

	/**
	 * Annotates table of contents data with Vector-specific information.
	 *
	 * In tableOfContents.js we have tableOfContents::getTableOfContentsSectionsData(),
	 * that yields the same result as this function, please make sure to keep them in sync.
	 *
	 * @param array $tocData
	 * @return array
	 */
	private function getTocData( array $tocData ): array {
		// If the table of contents has no items, we won't output it.
		// empty array is interpreted by Mustache as falsey.
		if ( empty( $tocData ) || empty( $tocData[ 'array-sections' ] ) ) {
			return [];
		}

		// Populate button labels for collapsible TOC sections
		foreach ( $tocData[ 'array-sections' ] as &$section ) {
			if ( $section['is-top-level-section'] && $section['is-parent-section'] ) {
				$section['vector-button-label'] =
					$this->msg( 'vector-toc-toggle-button-label', $section['line'] )->text();
			}
		}

		// ToC is pinned by default, hardcoded for now
		$isTocPinned = true;
		$pinnableHeader = new VectorComponentPinnableHeader(
			$this->getContext(),
			$isTocPinned,
			'vector-toc',
			false
		);

		return array_merge( $tocData, [
			'is-vector-toc-beginning-enabled' => $this->getConfig()->get(
				'VectorTableOfContentsBeginning'
			),
			'vector-is-collapse-sections-enabled' =>
				$tocData[ 'number-section-count'] >= $this->getConfig()->get(
					'VectorTableOfContentsCollapseAtCount'
				),
			'data-pinnable-header' => $pinnableHeader->getTemplateData(),
		] );
	}

	/**
	 * Returns pinnable header template data for page tools
	 *
	 * @param bool $isPinned
	 * @return array
	 */
	private function getPageToolsPinnableHeaderData( bool $isPinned ): array {
		return [
			'is-pinned' => $isPinned,
			'label' => $this->msg( 'vector-page-tools-label' ),
			'unpin-label' => $this->msg( 'vector-unpin-element-label' ),
			'pin-label' => $this->msg( 'vector-pin-element-label' ),
			'data-name' => 'vector-page-tools',
			'data-pinnable-element-id' => 'vector-page-tools-content',
			'data-unpinned-container-id' => 'vector-page-tools-content-container',
			'data-pinned-container-id' => 'vector-page-tools-pinned-container'
		];
	}

	/**
	 * @param array $portletData
	 * @return array
	 */
	private function getPageToolsData( array $portletData ): array {
		$actionsMenu = $portletData[ 'data-actions' ];
		$menusData = [ $actionsMenu ];
		// Page Tools is unpinned by default, hardcoded for now
		$isPageToolsPinned = false;

		$pinnableDropdownData = [
			'id' => 'vector-page-tools',
			'class' => 'vector-page-tools',
			'label' => $this->msg( 'toolbox' ),
			'is-pinned' => $isPageToolsPinned,
			// @phan-suppress-next-line PhanSuspiciousValueComparison
			'has-multiple-menus' => count( $menusData ) > 1,
			'data-pinnable-header' => $this->getPageToolsPinnableHeaderData( $isPageToolsPinned ),
			'data-menus' => $menusData
		];
		return $pinnableDropdownData;
	}

	/**
	 * Merges the `view-overflow` menu into the `action` menu.
	 * This ensures that the previous state of the menu e.g. emptyPortlet class
	 * is preserved.
	 * @param array $data
	 * @return array
	 */
	private function mergeViewOverflowIntoActions( $data ) {
		$portlets = $data['data-portlets'];
		$actions = $portlets['data-actions'];
		$overflow = $portlets['data-views-overflow'];
		// if the views overflow menu is not empty, then signal that the more menu despite
		// being initially empty now has collapsible items.
		if ( !$overflow['is-empty'] ) {
			$data['data-portlets']['data-actions']['class'] .= ' vector-has-collapsible-items';
		}
		$data['data-portlets']['data-actions']['html-items'] = $overflow['html-items'] . $actions['html-items'];
		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function getHtmlElementAttributes() {
		$original = parent::getHtmlElementAttributes();

		if ( VectorServices::getFeatureManager()->isFeatureEnabled( Constants::FEATURE_STICKY_HEADER ) ) {
			// T290518: Add scroll padding to root element when the sticky header is
			// enabled. This class needs to be server rendered instead of added from
			// JS in order to correctly handle situations where the sticky header
			// isn't visible yet but we still need scroll padding applied (e.g. when
			// the user navigates to a page with a hash fragment in the URI). For this
			// reason, we can't rely on the `vector-sticky-header-visible` class as it
			// is added too late.
			//
			// Please note that this class applies scroll padding which does not work
			// when applied to the body tag in Chrome, Safari, and Firefox (and
			// possibly others). It must instead be applied to the html tag.
			$original['class'] = implode( ' ', [ $original['class'] ?? '', self::STICKY_HEADER_ENABLED_CLASS ] );
		}

		return $original;
	}

	/**
	 * Determines wheather the initial state of sidebar is visible on not
	 *
	 * @return bool
	 */
	private function isMainMenuVisible() {
		$skin = $this->getSkin();
		if ( $skin->getUser()->isRegistered() ) {
			$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
			$userPrefSidebarState = $userOptionsLookup->getOption(
				$skin->getUser(),
				Constants::PREF_KEY_SIDEBAR_VISIBLE
			);

			$defaultLoggedinSidebarState = $this->getConfig()->get(
				Constants::CONFIG_KEY_DEFAULT_SIDEBAR_VISIBLE_FOR_AUTHORISED_USER
			);

			// If the sidebar user preference has been set, return that value,
			// if not, then the default sidebar state for logged-in users.
			return ( $userPrefSidebarState !== null )
				? (bool)$userPrefSidebarState
				: $defaultLoggedinSidebarState;
		}
		return $this->getConfig()->get(
			Constants::CONFIG_KEY_DEFAULT_SIDEBAR_VISIBLE_FOR_ANONYMOUS_USER
		);
	}

	/**
	 * @return array
	 */
	public function getTemplateData(): array {
		$featureManager = VectorServices::getFeatureManager();
		$parentData = parent::getTemplateData();

		$parentData['data-toc'] = $this->getTocData( $parentData['data-toc'] ?? [] );

		if ( !$this->isTableOfContentsVisibleInSidebar() ) {
			unset( $parentData['data-toc'] );
		}
		$parentData = $this->mergeViewOverflowIntoActions( $parentData );

		// FIXME: Move to component (T322089)
		$parentData['data-vector-user-links'] = $this->getUserLinksTemplateData(
			$parentData['data-portlets']['data-user-menu'],
			$parentData['data-portlets'][ 'data-vector-user-menu-overflow' ],
			$this->getUser()
		);
		$parentData['data-portlets']['data-variants'] = $this->updateVariantsMenuLabel(
			$parentData['data-portlets']['data-variants']
		);

		$langData = $parentData['data-portlets']['data-languages'] ?? null;
		if ( $langData ) {
			$parentData['data-lang-btn'] = $this->getULSPortletData(
				$langData,
				count( $this->getLanguagesCached() ),
				$this->isLanguagesInContentAt( 'top' )
			);
		}

		$isPageToolsEnabled = $featureManager->isFeatureEnabled( Constants::FEATURE_PAGE_TOOLS );
		if ( $isPageToolsEnabled ) {
			$pageToolsData = $this->getPageToolsData( $parentData['data-portlets'] );
			$parentData['data-portlets']['data-page-tools'] = $pageToolsData;
		}

		$config = $this->getConfig();
		$components = [
			'data-search-box' => new VectorComponentSearchBox(
				$parentData['data-search-box'],
				true,
				// is primary mode of search
				true,
				'searchform',
				true,
				$config,
				Constants::SEARCH_BOX_INPUT_LOCATION_MOVED,
				$this->getContext()
			),
			'data-portlets-main-menu' => new VectorComponentMainMenu(
				$parentData['data-portlets-sidebar'],
				$this,
				$this->shouldLanguageAlertBeInSidebar()
			),
		];
		foreach ( $components as $key => $component ) {
			// Array of components or null values.
			if ( $component ) {
				$parentData[$key] = $component->getTemplateData();
			}
		}

		$searchStickyHeader = new VectorComponentSearchBox(
			$parentData['data-search-box'],
			// Collapse inside search box is disabled.
			false,
			false,
			'vector-sticky-search-form',
			false,
			$config,
			Constants::SEARCH_BOX_INPUT_LOCATION_MOVED,
			$this->getContext()
		);

		return array_merge( $parentData, [
			'is-language-in-content' => $this->isLanguagesInContent(),
			'is-language-in-content-top' => $this->isLanguagesInContentAt( 'top' ),
			'is-language-in-content-bottom' => $this->isLanguagesInContentAt( 'bottom' ),
			'is-main-menu-visible' => $this->isMainMenuVisible(),
			// Cast empty string to null
			'html-subtitle' => $parentData['html-subtitle'] === '' ? null : $parentData['html-subtitle'],
			'data-vector-sticky-header' => $featureManager->isFeatureEnabled(
				Constants::FEATURE_STICKY_HEADER
			) ? $this->getStickyHeaderData(
				$searchStickyHeader->getTemplateData(),
				$featureManager->isFeatureEnabled(
					Constants::FEATURE_STICKY_HEADER_EDIT
				)
			) : false,
			'is-page-tools-enabled' => $isPageToolsEnabled
		] );
	}
}
