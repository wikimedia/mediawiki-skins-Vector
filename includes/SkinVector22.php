<?php

namespace Vector;

/**
 * @ingroup Skins
 * @package Vector
 * @internal
 */
class SkinVector22 extends SkinVector {
	/**
	 * Updates the constructor to conditionally disable table of contents in article
	 * body. Note, the constructor can only check feature flags that do not vary on
	 * whether the user is logged in e.g. features with the 'default' key set.
	 * @inheritDoc
	 */
	public function __construct( array $options ) {
		$options['toc'] = !$this->isTableOfContentsVisibleInSidebar();
		parent::__construct( $options );
	}

	/**
	 * Determines if the Table of Contents should be visible.
	 * TOC is visible on main namespaces except for the Main Page
	 * when the feature flag is on.
	 *
	 * @return bool
	 */
	private function isTableOfContentsVisibleInSidebar(): bool {
		$featureManager = VectorServices::getFeatureManager();
		$title = $this->getTitle();
		$isMainPage = $title ? $title->isMainPage() : false;
		return $featureManager->isFeatureEnabled( Constants::FEATURE_TABLE_OF_CONTENTS ) && !$isMainPage;
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
