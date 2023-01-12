<?php
namespace MediaWiki\Skins\Vector\Components;

use MessageLocalizer;

/**
 * VectorComponentStickyHeader component
 */
class VectorComponentStickyHeader implements VectorComponent {
	/** @var MessageLocalizer */
	private $localizer;

	/**
	 * @param MessageLocalizer $localizer
	 */
	public function __construct( MessageLocalizer $localizer ) {
		$this->localizer = $localizer;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$pinnableContainer = new VectorComponentTableOfContentsContainer(
			$this->localizer,
			'vector-sticky-header-toc'
		);
		$tocDropdown = new VectorComponentDropdown(
			'vector-toc',
			'',
			'mw-portlet mw-portlet-sticky-header-toc vector-sticky-header-toc',
			'listBullet'
		);
		return [
			'data-sticky-header-toc-pinnable-container' => $pinnableContainer->getTemplateData(),
			'data-sticky-header-toc-dropdown' => $tocDropdown->getTemplateData(),
		];
	}
}
