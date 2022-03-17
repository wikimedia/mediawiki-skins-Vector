<?php

namespace Vector;

use MediaWiki\MediaWikiServices;

/**
 * @ingroup Skins
 * @package Vector
 * @internal
 */
class SkinVector22 extends SkinVector {
	private const TOC_AB_TEST_NAME = 'skin-vector-toc-experiment';

	/**
	 * Updates the constructor to conditionally disable table of contents in article
	 * body. Note, the constructor can only check feature flags that do not vary on
	 * whether the user is logged in e.g. features with the 'default' key set.
	 * @inheritDoc
	 */
	public function __construct( array $options ) {
		if ( !$this->isTOCABTestEnabled() ) {
			$options['toc'] = !$this->isTableOfContentsVisibleInSidebar();
		}

		parent::__construct( $options );
	}

	/**
	 * @internal
	 * @return bool
	 */
	public function isTOCABTestEnabled(): bool {
		$experimentConfig = $this->getConfig()->get( Constants::CONFIG_WEB_AB_TEST_ENROLLMENT );

		return $experimentConfig['name'] === self::TOC_AB_TEST_NAME &&
			$experimentConfig['enabled'] &&
			MediaWikiServices::getInstance()->hasService( Constants::WEB_AB_TEST_ARTICLE_ID_FACTORY_SERVICE );
	}

	/**
	 * Returns whether or not the table of contents is enabled through
	 * FeatureManager.
	 *
	 * @internal
	 * @return bool
	 */
	public function isTOCEnabled() {
		$featureManager = VectorServices::getFeatureManager();

		return $featureManager->isFeatureEnabled( Constants::FEATURE_TABLE_OF_CONTENTS );
	}

	/**
	 * Determines if the Table of Contents should be visible.
	 * TOC is visible on main namespaces except for the Main Page
	 * when the feature flag is on.
	 *
	 * @internal
	 * @return bool
	 */
	public function isTableOfContentsVisibleInSidebar(): bool {
		$title = $this->getTitle();

		if (
			!$title ||
			$title->getArticleID() === 0 ||
			$title->isMainPage()
		) {
			return false;
		}

		if ( $this->isTOCABTestEnabled() ) {
			return true;
		}

		return $this->isTOCEnabled();
	}

	/**
	 * Temporary function while we deprecate SkinVector class.
	 *
	 * @return bool
	 */
	protected function isLegacy(): bool {
		return false;
	}

	/**
	 * @return array
	 */
	public function getTemplateData(): array {
		$featureManager = VectorServices::getFeatureManager();
		$parentData = parent::getTemplateData();
		if ( !$this->isTableOfContentsVisibleInSidebar() ) {
			unset( $parentData['data-toc'] );
		}
		return $parentData + [
			'data-vector-sticky-header' => $featureManager->isFeatureEnabled(
				Constants::FEATURE_STICKY_HEADER
			) ? $this->getStickyHeaderData(
				$this->getSearchData(
					$parentData['data-search-box'],
					// Collapse inside search box is disabled.
					false,
					false,
					'vector-sticky-search-form',
					false
				),
				$featureManager->isFeatureEnabled(
					Constants::FEATURE_STICKY_HEADER_EDIT
				)
			) : false,
		];
	}
}
