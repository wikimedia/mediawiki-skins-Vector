<?php

namespace MediaWiki\Skins\Vector;

/**
 * @ingroup Skins
 * @package Vector
 * @internal
 */
class SkinVectorLegacy extends SkinVector {
	/**
	 * Whether or not the legacy version of the skin is being used.
	 *
	 * @return bool
	 */
	protected function isLegacy(): bool {
		return true;
	}

	/**
	 * Show the ULS button if it's modern Vector, languages in header is enabled,
	 * and the ULS extension is enabled. Hide it otherwise.
	 * There is no point in showing the language button if ULS extension is unavailable
	 * as there is no ways to add languages without it.
	 * @return bool
	 */
	protected function shouldHideLanguages(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function isLanguagesInContentAt( $location ) {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$parentData = parent::getTemplateData();

		// SkinVector sometimes serves new Vector as part of removing the
		// skin version user preference. To avoid T302461 we need to unset it here.
		// This shouldn't be run on SkinVector22.
		unset( $parentData['data-toc'] );
		return $parentData;
	}
}
