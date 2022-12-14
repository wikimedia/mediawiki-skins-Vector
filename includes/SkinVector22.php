<?php

namespace MediaWiki\Skins\Vector;

use MediaWiki\MediaWikiServices;
use MediaWiki\Skins\Vector\Components\VectorComponentDropdown;
use MediaWiki\Skins\Vector\Components\VectorComponentMainMenu;
use MediaWiki\Skins\Vector\Components\VectorComponentPageTools;
use MediaWiki\Skins\Vector\Components\VectorComponentPinnableHeader;
use MediaWiki\Skins\Vector\Components\VectorComponentSearchBox;
use MediaWiki\Skins\Vector\Components\VectorComponentStickyHeader;
use MediaWiki\Skins\Vector\Components\VectorComponentTableOfContents;

/**
 * @ingroup Skins
 * @package Vector
 * @internal
 */
class SkinVector22 extends SkinVector {
	private const STICKY_HEADER_ENABLED_CLASS = 'vector-sticky-header-enabled';

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
	 * Annotates table of contents data with Vector-specific information.
	 *
	 * In tableOfContents.js we have tableOfContents::getTableOfContentsSectionsData(),
	 * that yields the same result as this function, please make sure to keep them in sync.
	 * FIXME: This code should be moved to VectorComponentTableOfContents.
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
			null,
			false,
			'h2'
		);

		return array_merge( $tocData, [
			'is-vector-toc-beginning-enabled' => $this->getConfig()->get(
				'VectorTableOfContentsBeginning'
			),
			'vector-is-collapse-sections-enabled' =>
				$tocData[ 'number-section-count'] >= $this->getConfig()->get(
					'VectorTableOfContentsCollapseAtCount'
				),
			'is-pinned' => $isTocPinned,
			'data-pinnable-header' => $pinnableHeader->getTemplateData(),
		] );
	}

	/**
	 * @return array
	 */
	private function getTocPageTitleData(): array {
		return Hooks::updateDropdownMenuData( [
			'id' => 'vector-page-titlebar-toc',
			'class' => 'vector-page-titlebar-toc mw-ui-icon-flush-left',
			'is-pinned' => true,
			'button' => true,
			'text-hidden' => true,
			'icon' => 'listBullet'
		] );
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
		$stickyHeader = new VectorComponentStickyHeader();
		$toc = new VectorComponentTableOfContents();
		$tocData = $parentData['data-toc'] ?? [];
		$parentData['data-toc'] = !empty( $tocData ) ?
			$toc->getTemplateData() + $this->getTocData( $tocData ) : null;

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
		$isPageToolsPinned = $featureManager->isFeatureEnabled( Constants::FEATURE_PAGE_TOOLS_PINNED );
		$sidebar = $parentData[ 'data-portlets-sidebar' ];
		$pageToolsMenus = [];
		$restPortlets = $parentData[ 'data-portlets-sidebar' ][ 'array-portlets-rest' ];
		if ( $isPageToolsEnabled ) {
			$toolboxMenuIndex = array_search(
				VectorComponentPageTools::TOOLBOX_ID,
				array_column(
					$restPortlets,
					'id'
				)
			);

			if ( $toolboxMenuIndex !== false ) {
				// Splice removes the toolbox menu from the $restPortlets array
				// and current returns the first value of array_splice, i.e. the $toolbox menu data.
				$pageToolsMenus = array_splice( $restPortlets, $toolboxMenuIndex );
				$parentData[ 'data-portlets-sidebar' ]['array-portlets-rest'] = $restPortlets;
				$sidebar = $parentData[ 'data-portlets-sidebar' ];
			}
		}
		$config = $this->getConfig();
		$mainMenuDropdownClass = $this->getUser()->isAnon() ? 'vector-main-menu-btn-dropdown-anon ' : '';
		$mainMenuDropdownClass .= 'vector-main-menu-dropdown';
		$mainMenuDropdown = new VectorComponentDropdown(
			'vector-main-menu-dropdown',
			$this->msg( 'vector-main-menu-label' )->text(),
			$mainMenuDropdownClass,
			'menu'
		);

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
			'data-main-menu-dropdown' => $mainMenuDropdown,
			'data-portlets-main-menu' => new VectorComponentMainMenu(
				$sidebar,
				$this,
				$this->shouldLanguageAlertBeInSidebar(),
				$parentData['data-portlets']['data-languages'] ?? [],
			),
			'data-page-tools' => $isPageToolsEnabled ? new VectorComponentPageTools(
				array_merge( [ $parentData['data-portlets']['data-actions'] ?? [] ], $pageToolsMenus ),
				$isPageToolsPinned,
				$this->getContext(),
				$this->getUser()
			) : null,
			'data-page-tools-dropdown' => $isPageToolsEnabled ?
				new VectorComponentDropdown( 'vector-page-tools', $this->msg( 'toolbox' )->text() ) : null,
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
			'data-page-titlebar-toc' => $this->getTocPageTitleData(),
			'data-vector-sticky-header' => $featureManager->isFeatureEnabled(
				Constants::FEATURE_STICKY_HEADER
			) ? $stickyHeader->getTemplateData() + $this->getStickyHeaderData(
				$searchStickyHeader->getTemplateData(),
				$featureManager->isFeatureEnabled(
					Constants::FEATURE_STICKY_HEADER_EDIT
				)
			) : false,
			'is-page-tools-enabled' => $isPageToolsEnabled
		] );
	}
}
