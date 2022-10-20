<?php
/**
 * Vector - Modern version of MonoBook with fresh look and many usability
 * improvements.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Skins
 */

namespace MediaWiki\Skins\Vector;

use ExtensionRegistry;
use Linker;
use MediaWiki\MediaWikiServices;
use RuntimeException;
use SkinMustache;
use SkinTemplate;
use Title;
use User;

/**
 * Skin subclass for Vector that may be the new or old version of Vector.
 * IMPORTANT: DO NOT put new code here.
 *
 * @ingroup Skins
 * Skins extending SkinVector are not supported
 *
 * @package Vector
 * @internal
 *
 * @todo
 *  - Move all modern code into SkinVector22.
 *  - Move legacy skin code from SkinVector to SkinVectorLegacy.
 *  - SkinVector left as alias if necessary.
 */
abstract class SkinVector extends SkinMustache {
	/** @var null|array for caching purposes */
	private $languages;
	private const TALK_ICON = [
		'href' => '#',
		'id' => 'ca-talk-sticky-header',
		'event' => 'talk-sticky-header',
		'icon' => 'wikimedia-speechBubbles',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	private const SUBJECT_ICON = [
		'href' => '#',
		'id' => 'ca-subject-sticky-header',
		'event' => 'subject-sticky-header',
		'icon' => 'wikimedia-article',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	private const HISTORY_ICON = [
		'href' => '#',
		'id' => 'ca-history-sticky-header',
		'event' => 'history-sticky-header',
		'icon' => 'wikimedia-history',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	// Event and icon will be updated depending on watchstar state
	private const WATCHSTAR_ICON = [
		'href' => '#',
		'id' => 'ca-watchstar-sticky-header',
		'event' => 'watch-sticky-header',
		'icon' => 'wikimedia-star',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon mw-watchlink'
	];
	private const EDIT_VE_ICON = [
		'href' => '#',
		'id' => 'ca-ve-edit-sticky-header',
		'event' => 've-edit-sticky-header',
		'icon' => 'wikimedia-edit',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	private const EDIT_WIKITEXT_ICON = [
		'href' => '#',
		'id' => 'ca-edit-sticky-header',
		'event' => 'wikitext-edit-sticky-header',
		'icon' => 'wikimedia-wikiText',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	private const EDIT_PROTECTED_ICON = [
		'href' => '#',
		'id' => 'ca-viewsource-sticky-header',
		'event' => 've-edit-protected-sticky-header',
		'icon' => 'wikimedia-editLock',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	private const CLASS_PROGRESSIVE = 'mw-ui-progressive';

	/**
	 * Calls getLanguages with caching.
	 * @return array
	 */
	protected function getLanguagesCached(): array {
		if ( $this->languages === null ) {
			$this->languages = $this->getLanguages();
		}
		return $this->languages;
	}

	/**
	 * This should be upstreamed to the Skin class in core once the logic is finalized.
	 * Returns false if the page is a special page without any languages, or if an action
	 * other than view is being used.
	 * @return bool
	 */
	private function canHaveLanguages(): bool {
		if ( $this->getContext()->getActionName() !== 'view' ) {
			return false;
		}
		$title = $this->getTitle();
		// Defensive programming - if a special page has added languages explicitly, best to show it.
		if ( $title && $title->isSpecialPage() && empty( $this->getLanguagesCached() ) ) {
			return false;
		}
		return true;
	}

	/**
	 * @param string $location Either 'top' or 'bottom' is accepted.
	 * @return bool
	 */
	protected function isLanguagesInContentAt( $location ) {
		if ( !$this->canHaveLanguages() ) {
			return false;
		}
		$featureManager = VectorServices::getFeatureManager();
		$inContent = $featureManager->isFeatureEnabled(
			Constants::FEATURE_LANGUAGE_IN_HEADER
		);
		$isMainPage = $this->getTitle() ? $this->getTitle()->isMainPage() : false;

		switch ( $location ) {
			case 'top':
				return $isMainPage ? $inContent && $featureManager->isFeatureEnabled(
					Constants::FEATURE_LANGUAGE_IN_MAIN_PAGE_HEADER
				) : $inContent;
			case 'bottom':
				return $inContent && $isMainPage && !$featureManager->isFeatureEnabled(
					Constants::FEATURE_LANGUAGE_IN_MAIN_PAGE_HEADER
				);
			default:
				throw new RuntimeException( 'unknown language button location' );
		}
	}

	/**
	 * Whether or not the languages are out of the sidebar and in the content either at
	 * the top or the bottom.
	 * @return bool
	 */
	final protected function isLanguagesInContent() {
		return $this->isLanguagesInContentAt( 'top' ) || $this->isLanguagesInContentAt( 'bottom' );
	}

	/**
	 * Whether languages should be hidden.
	 * FIXME: Function should be removed as part of T319355
	 *
	 * @return bool
	 */
	abstract protected function shouldHideLanguages(): bool;

	/**
	 * Returns HTML for the create account link inside the anon user links
	 * @param string[] $returnto array of query strings used to build the login link
	 * @return string
	 */
	private function getCreateAccountHTML( $returnto ) {
		$createAccountData = $this->buildCreateAccountData( $returnto );
		$createAccountData = array_merge( $createAccountData, [
			'class' => [
				'vector-menu-content-item',
			],
			'collapsible' => true,
			'icon' => $createAccountData['icon'],
			'button' => false
		] );
		$createAccountData = Hooks::updateLinkData( $createAccountData );
		return $this->makeLink( 'create-account', $createAccountData );
	}

	/**
	 * Returns HTML for the create account button, login button and learn more link inside the anon user menu
	 * @param string[] $returnto array of query strings used to build the login link
	 * @param bool $useCombinedLoginLink if a combined login/signup link will be used
	 * @param bool $isTempUser
	 * @param bool $includeLearnMoreLink Pass `true` to include the learn more
	 * link in the menu for anon users. This param will be inert for temp users.
	 * @return array
	 */
	private function getAnonMenuBeforePortletData(
		$returnto,
		$useCombinedLoginLink,
		$isTempUser,
		$includeLearnMoreLink
	): array {
		$templateParser = $this->getTemplateParser();
		$loginLinkData = array_merge( $this->buildLoginData( $returnto, $useCombinedLoginLink ), [
			'class' => [ 'vector-menu-content-item', 'vector-menu-content-item-login' ],
		] );
		$loginLinkData = Hooks::updateLinkData( $loginLinkData );
		$templateData = [
			'htmlCreateAccount' => $this->getCreateAccountHTML( $returnto ),
			'htmlLogin' => $this->makeLink( 'login', $loginLinkData ),
			'data-anon-editor' => []
		];

		if ( !$isTempUser && $includeLearnMoreLink ) {
			$learnMoreLinkData = [
				'text' => $this->msg( 'vector-anon-user-menu-pages-learn' )->text(),
				'href' => Title::newFromText( $this->msg( 'vector-intro-page' )->text() )->getLocalURL(),
				'aria-label' => $this->msg( 'vector-anon-user-menu-pages-label' )->text(),
			];

			$templateData['data-anon-editor'] = [
				'htmlLearnMoreLink' => $this->makeLink( '', $learnMoreLinkData ),
				'msgLearnMore' => $this->msg( 'vector-anon-user-menu-pages' )
			];
		}

		return $templateData;
	}

	/**
	 * Returns HTML for the logout button that should be placed in the user (personal) menu
	 * after the menu itself.
	 * @return string
	 */
	private function getLogoutHTML(): string {
		$logoutLinkData = array_merge( $this->buildLogoutLinkData(), [
			'class' => [ 'vector-menu-content-item', 'vector-menu-content-item-logout' ],
		] );
		return $this->makeLink( 'logout', Hooks::updateLinkData( $logoutLinkData ) );
	}

	/**
	 * Returns template data for UserLinks.mustache
	 * FIXME: Move to component (T322089)
	 *
	 * @param array $userMenuData existing menu template data to be transformed and copied for UserLinks
	 * @param array $overflowMenuData existing menu template data to be transformed and copied for UserLinks
	 * @param User $user the context user
	 * @return array
	 */
	final protected function getUserLinksTemplateData(
		array $userMenuData, array $overflowMenuData, User $user
	): array {
		$isAnon = !$user->isRegistered();
		$isTempUser = $user->isTemp();
		$returnto = $this->getReturnToParam();
		$useCombinedLoginLink = $this->useCombinedLoginLink();
		$userMenuOverflowData = Hooks::updateDropdownMenuData( $overflowMenuData );
		$userMenuData = Hooks::updateDropdownMenuData( $this->getUserMenuPortletData( $userMenuData ) );
		unset( $userMenuOverflowData[ 'label' ] );

		if ( $isAnon || $isTempUser ) {
			$additionalData = $this->getAnonMenuBeforePortletData(
				$returnto,
				$useCombinedLoginLink,
				$isTempUser,
				// T317789: The `anontalk` and `anoncontribs` links will not be added to
				// the menu if `$wgGroupPermissions['*']['edit']` === false which can
				// leave the menu empty due to our removal of other user menu items in
				// `Hooks::updateUserLinksDropdownItems`. In this case, we do not want
				// to render the anon "learn more" link.
				!$userMenuData['is-empty']
			);
		} else {
			$additionalData = [];
		}

		$moreItems = substr_count( $userMenuOverflowData['html-items'], '<li' );
		return $additionalData + [
			'html-logout-link' => $this->getLogoutHTML(),
			'is-temp-user' => $isTempUser,
			'is-wide' => $moreItems > 3,
			'data-user-menu-overflow' => $userMenuOverflowData,
			'data-user-menu' => $userMenuData
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function runOnSkinTemplateNavigationHooks( SkinTemplate $skin, &$content_navigation ) {
		parent::runOnSkinTemplateNavigationHooks( $skin, $content_navigation );
		Hooks::onSkinTemplateNavigation( $skin, $content_navigation );
	}

	/**
	 * Check whether ULS is enabled
	 *
	 * @return bool
	 */
	final protected function isULSExtensionEnabled(): bool {
		return ExtensionRegistry::getInstance()->isLoaded( 'UniversalLanguageSelector' );
	}

	/**
	 * Change the portlets menu so the label is the selected variant
	 * @param array $portletData
	 * @return array
	 */
	final protected function updateVariantsMenuLabel( array $portletData ): array {
		$languageConverterFactory = MediaWikiServices::getInstance()->getLanguageConverterFactory();
		$pageLang = $this->getTitle()->getPageLanguage();
		$converter = $languageConverterFactory->getLanguageConverter( $pageLang );
		$portletData['label'] = $pageLang->getVariantname(
			$converter->getPreferredVariant()
		);
		// T289523 Add aria-label data to the language variant switcher.
		$portletData['aria-label'] = $this->msg( 'vector-language-variant-switcher-label' );
		return $portletData;
	}

	/**
	 * Generate data needed to generate the sticky header.
	 * @param array $searchBoxData
	 * @param bool $includeEditIcons
	 * @return array
	 */
	final protected function getStickyHeaderData( $searchBoxData, $includeEditIcons ): array {
		$btns = [
			self::TALK_ICON,
			self::SUBJECT_ICON,
			self::HISTORY_ICON,
			self::WATCHSTAR_ICON,
		];
		if ( $includeEditIcons ) {
			$btns[] = self::EDIT_WIKITEXT_ICON;
			$btns[] = self::EDIT_PROTECTED_ICON;
			$btns[] = self::EDIT_VE_ICON;
		}
		$btns[] = $this->getAddSectionButtonData();

		$tocPortletData = Hooks::updateDropdownMenuData( [
			'id' => 'vector-sticky-header-toc',
			'class' => 'mw-portlet mw-portlet-sticky-header-toc vector-sticky-header-toc',
			'html-vector-menu-checkbox-attributes' => 'tabindex="-1"',
			'html-vector-menu-heading-attributes' => 'tabindex="-1"',
			'is-pinned' => true,
			'button' => true,
			'text-hidden' => true,
			'icon' => 'listBullet'
		] );

		// Show sticky ULS if the ULS extension is enabled and the ULS in header is not hidden
		$showStickyULS = $this->isULSExtensionEnabled() && !$this->shouldHideLanguages();
		return [
			'data-sticky-header-toc' => $tocPortletData,
			'data-primary-action' => $showStickyULS ?
				$this->getULSButtonData() : null,
			'data-button-start' => [
				'label' => $this->msg( 'search' ),
				'icon' => 'wikimedia-search',
				'is-quiet' => true,
				'tabindex' => '-1',
				'class' => 'vector-sticky-header-search-toggle',
				'event' => 'ui.' . $searchBoxData['form-id'] . '.icon'
			],
			'data-search' => $searchBoxData,
			'data-buttons' => $btns,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function isResponsive() {
		// Check it's enabled by user preference and configuration
		$responsive = parent::isResponsive() && $this->getConfig()->get( 'VectorResponsive' );
		// For historic reasons, the viewport is added when Vector is loaded on the mobile
		// domain. This is only possible for 3rd parties or by useskin parameter as there is
		// no preference for changing mobile skin. Only need to check if $responsive is falsey.
		if ( !$responsive && ExtensionRegistry::getInstance()->isLoaded( 'MobileFrontend' ) ) {
			$mobFrontContext = MediaWikiServices::getInstance()->getService( 'MobileFrontend.Context' );
			if ( $mobFrontContext->shouldDisplayMobileView() ) {
				return true;
			}
		}
		return $responsive;
	}

	/**
	 * Get the ULS button label, accounting for the number of available
	 * languages.
	 *
	 * @return array
	 */
	private function getULSLabels(): array {
		$numLanguages = count( $this->getLanguagesCached() );

		if ( $numLanguages === 0 ) {
			return [
				'label' => $this->msg( 'vector-no-language-button-label' )->text(),
				'aria-label' => $this->msg( 'vector-no-language-button-aria-label' )->text()
			];
		} else {
			return [
				'label' => $this->msg( 'vector-language-button-label' )->numParams( $numLanguages )->escaped(),
				'aria-label' => $this->msg( 'vector-language-button-aria-label' )->numParams( $numLanguages )->escaped()
			];
		}
	}

	/**
	 * Creates button data for the "Add section" button in the sticky header
	 *
	 * @return array
	 */
	private function getAddSectionButtonData() {
		return [
			'href' => '#',
			'id' => 'ca-addsection-sticky-header',
			'event' => 'addsection-sticky-header',
			'html-vector-button-icon' => Hooks::makeIcon( 'wikimedia-speechBubbleAdd-progressive' ),
			'label' => $this->msg( [ 'vector-2022-action-addsection', 'skin-action-addsection' ] ),
			'is-quiet' => true,
			'tabindex' => '-1',
			'class' => 'sticky-header-icon mw-ui-primary mw-ui-progressive'
		];
	}

	/**
	 * Creates button data for the ULS button in the sticky header
	 *
	 * @return array
	 */
	private function getULSButtonData() {
		$numLanguages = count( $this->getLanguagesCached() );

		return [
			'id' => 'p-lang-btn-sticky-header',
			'class' => 'mw-interlanguage-selector',
			'is-quiet' => true,
			'tabindex' => '-1',
			'label' => $this->getULSLabels()[ 'label' ],
			'html-vector-button-icon' => Hooks::makeIcon( 'wikimedia-language' ),
			'event' => 'ui.dropdown-p-lang-btn-sticky-header'
		];
	}

	/**
	 * Creates portlet data for the ULS button in the header
	 *
	 * @param array $langData
	 * @param int $numLanguages
	 * @param bool $atTop
	 * @return array
	 */
	final protected function getULSPortletData( array $langData, int $numLanguages, bool $atTop ) {
		$className = $langData['class'] ?? '';
		$classNameSuffix = $atTop ? ' mw-ui-icon-flush-right' : '';

		$languageButtonData = [
			'id' => 'p-lang-btn',
			'label' => $this->getULSLabels()['label'],
			'aria-label' => $this->getULSLabels()['aria-label'],
			// ext.uls.interface attaches click handler to this selector.
			'checkbox-class' => ' mw-interlanguage-selector ',
			'icon' => 'language-progressive',
			'class' => $className . $classNameSuffix,
			'button' => true,
			'heading-class' => self::CLASS_PROGRESSIVE . ' mw-portlet-lang-heading-' . strval( $numLanguages )
		];

		// Adds class to hide language button
		// Temporary solution to T287206, can be removed when ULS dialog includes interwiki links
		if ( $this->shouldHideLanguages() ) {
			$languageButtonData['class'] .= ' mw-portlet-empty';
		}

		return Hooks::updateDropdownMenuData(
			array_merge(
				$langData, $languageButtonData
			)
		);
	}

	/**
	 * Creates portlet data for the user menu dropdown
	 * FIXME: Move to SkinVector22
	 *
	 * @param array $portletData
	 * @return array
	 */
	private function getUserMenuPortletData( $portletData ) {
		// T317789: Core can undesirably add an 'emptyPortlet' class that hides the
		// user menu. This is a result of us manually removing items from the menu
		// in Hooks::updateUserLinksDropdownItems which can make
		// SkinTemplate::getPortletData apply the `emptyPortlet` class if there are
		// no menu items. Since we subsequently add menu items in
		// SkinVector::getUserLinksTemplateData, the `emptyPortlet` class is
		// innaccurate. This is why we add the desired classes, `mw-portlet` and
		// `mw-portlet-personal` here instead. This can potentially be removed upon
		// completion of T319356.
		//
		// Also, add target class to apply different icon to personal menu dropdown for logged in users.
		$portletData['class'] = 'mw-portlet mw-portlet-personal vector-user-menu vector-menu-dropdown';
		$portletData['class'] .= $this->loggedin ?
			' vector-user-menu-logged-in' :
			' vector-user-menu-logged-out';
		if ( $this->getUser()->isTemp() ) {
			$icon = 'userAnonymous';
		} elseif ( $this->loggedin ) {
			$icon = 'userAvatar';
		} else {
			$icon = 'ellipsis';
			// T287494 We use tooltip messages to provide title attributes on hover over certain menu icons.
			// For modern Vector, the "tooltip-p-personal" key is set to "User menu" which is appropriate for
			// the user icon (dropdown indicator for user links menu) for logged-in users.
			// This overrides the tooltip for the user links menu icon which is an ellipsis for anonymous users.
			$portletData['html-tooltip'] = Linker::tooltip( 'vector-anon-user-menu-title' );
		}
		$portletData['icon'] = $icon;
		$portletData['button'] = true;
		$portletData['text-hidden'] = true;
		return $portletData;
	}
}
