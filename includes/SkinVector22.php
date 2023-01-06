<?php

namespace MediaWiki\Skins\Vector;

use MediaWiki\MediaWikiServices;
use MediaWiki\Skins\Vector\Components\VectorComponentDropdown;
use MediaWiki\Skins\Vector\Components\VectorComponentIconLink;
use MediaWiki\Skins\Vector\Components\VectorComponentLanguageDropdown;
use MediaWiki\Skins\Vector\Components\VectorComponentMainMenu;
use MediaWiki\Skins\Vector\Components\VectorComponentMenu;
use MediaWiki\Skins\Vector\Components\VectorComponentMenuListItem;
use MediaWiki\Skins\Vector\Components\VectorComponentMenuVariants;
use MediaWiki\Skins\Vector\Components\VectorComponentPageTools;
use MediaWiki\Skins\Vector\Components\VectorComponentPinnableContainer;
use MediaWiki\Skins\Vector\Components\VectorComponentSearchBox;
use MediaWiki\Skins\Vector\Components\VectorComponentStickyHeader;
use MediaWiki\Skins\Vector\Components\VectorComponentTableOfContents;
use MediaWiki\Skins\Vector\Components\VectorComponentTableOfContentsContainer;
use MediaWiki\Skins\Vector\Components\VectorComponentUserLinks;

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
	 * Pulls the page tools menu out of $sidebar into $pageToolsMenu
	 *
	 * @param array &$sidebar
	 * @param array &$pageToolsMenu
	 */
	private static function extractPageToolsFromSidebar( &$sidebar, &$pageToolsMenu ) {
		$restPortlets = $sidebar[ 'array-portlets-rest' ] ?? [];
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
			$pageToolsMenu = array_splice( $restPortlets, $toolboxMenuIndex );
			$sidebar['array-portlets-rest'] = $restPortlets;
		}
	}

	/**
	 * @return array
	 */
	public function getTemplateData(): array {
		$featureManager = VectorServices::getFeatureManager();
		$parentData = parent::getTemplateData();
		$localizer = $this->getContext();
		$stickyHeader = new VectorComponentStickyHeader( $localizer );
		$parentData = $this->mergeViewOverflowIntoActions( $parentData );
		$portlets = $parentData['data-portlets'];

		$langData = $parentData['data-portlets']['data-languages'] ?? null;
		$config = $this->getConfig();

		$isPageToolsEnabled = $featureManager->isFeatureEnabled( Constants::FEATURE_PAGE_TOOLS );
		$sidebar = $parentData[ 'data-portlets-sidebar' ];
		$pageToolsMenu = [];
		if ( $isPageToolsEnabled ) {
			self::extractPageToolsFromSidebar( $sidebar, $pageToolsMenu );
		}

		$langButtonClass = $langData['class'] ?? '';
		$ulsLabels = $this->getULSLabels();
		$returnto = $this->getReturnToParam();
		$user = $this->getUser();
		$logoutData = $this->buildLogoutLinkData();
		$loginLinkData = $this->buildLoginData( $returnto, $this->useCombinedLoginLink() );
		$createAccountData = $this->buildCreateAccountData( $returnto );
		$components = [
			'data-vector-variants' => new VectorComponentMenuVariants(
				$parentData['data-portlets']['data-variants'],
				$this->getTitle()->getPageLanguage(),
				$this->msg( 'vector-language-variant-switcher-label' )
			),
			'data-vector-user-links' => new VectorComponentUserLinks(
				$this->getContext(),
				$user,
				new VectorComponentMenu(
					$portlets['data-user-menu']
				),
				new VectorComponentMenu( [
					// this menu should have no label
					'label' => '',
				] + $portlets[ 'data-vector-user-menu-overflow' ] ),
				new VectorComponentMenu(
					[],
					$user->isRegistered() ? [
						new VectorComponentMenuListItem(
							new VectorComponentIconLink(
								$logoutData[ 'href'],
								$logoutData[ 'text' ],
								$logoutData[ 'icon' ],
								$this,
								'pt-logout'
							),
							'vector-user-menu-logout',
							'pt-logout'
						)
					] : [
						new VectorComponentMenuListItem(
							new VectorComponentIconLink(
								$createAccountData['href'] ?? null,
								$createAccountData['text'] ?? null,
								$createAccountData['icon'] ?? null,
								$this,
								'create-account'
							),
							'vector-user-menu-create-account user-links-collapsible-item'
						),
						new VectorComponentMenuListItem(
							new VectorComponentIconLink(
								$loginLinkData['href'] ?? null,
								$loginLinkData['text'] ?? null,
								$loginLinkData['icon'] ?? null,
								$this,
								'login'
							),
							'vector-user-menu-login'
						)
					]
				),
			),
			'data-lang-btn' => $langData ? new VectorComponentLanguageDropdown(
				$ulsLabels['label'],
				$ulsLabels['aria-label'],
				$this->isLanguagesInContentAt( 'top' ) ?
					$langButtonClass . ' mw-ui-icon-flush-right' : $langButtonClass,
				count( $this->getLanguagesCached() ),
				$langData['html-items'] ?? '',
				$langData['html-before-portal'] ?? '',
				$langData['html-after-portal'] ?? '',
				$this->getTitle()
			) : null,
			'data-toc-container' => new VectorComponentTableOfContentsContainer(
				$localizer,
			),
			'data-toc' => new VectorComponentTableOfContents(
				$parentData['data-toc'],
				$localizer,
				$this->getConfig()
			),
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
			'data-main-menu' => new VectorComponentMainMenu(
				$sidebar,
				$this->shouldLanguageAlertBeInSidebar(),
				$parentData['data-portlets']['data-languages'] ?? [],
				$this->getContext(),
				$this->getUser(),
				VectorServices::getFeatureManager(),
				$this,
			),
			'data-main-menu-dropdown' => new VectorComponentDropdown(
				VectorComponentMainMenu::ID . '-dropdown',
				$this->msg( VectorComponentMainMenu::ID . '-label' )->text(),
				VectorComponentMainMenu::ID . '-dropdown' . ' mw-ui-icon-flush-left mw-ui-icon-flush-right',
				'menu'
			),
			'data-page-tools' => $isPageToolsEnabled ? new VectorComponentPageTools(
				array_merge( [ $parentData['data-portlets']['data-actions'] ?? [] ], $pageToolsMenu ),
				$this->getContext(),
				$this->getUser(),
				$featureManager
			) : null,
			'data-page-tools-dropdown' => $isPageToolsEnabled ? new VectorComponentDropdown(
				VectorComponentPageTools::ID . '-dropdown',
				$this->msg( 'toolbox' )->text(),
				VectorComponentPageTools::ID . '-dropdown',
			) : null,
			'data-page-titlebar-toc-dropdown' => new VectorComponentDropdown(
				'vector-page-titlebar-toc',
				// no label,
				'',
				// class
				'vector-page-titlebar-toc mw-ui-icon-flush-left',
				// icon
				'listBullet',
			),
			'data-page-titlebar-pinnable-container' => new VectorComponentPinnableContainer(
				'vector-page-titlebar-toc',
				true
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
