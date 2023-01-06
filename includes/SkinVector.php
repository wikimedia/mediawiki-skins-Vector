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
use MediaWiki\MediaWikiServices;
use MediaWiki\Skins\Vector\Components\VectorComponentLanguageButton;
use RuntimeException;
use SkinMustache;
use SkinTemplate;

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
	 * Generate data needed to generate the sticky header.
	 * FIXME: Move to VectorComponentStickyHeader
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

		// Show sticky ULS if the ULS extension is enabled and the ULS in header is not hidden
		$showStickyULS = $this->isULSExtensionEnabled() && !$this->shouldHideLanguages();
		$langButton = new VectorComponentLanguageButton( $this->getULSLabels()[ 'label' ] );

		return [
			'data-primary-action' => $showStickyULS ?
				$langButton->getTemplateData() : null,
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
	final protected function getULSLabels(): array {
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
}
