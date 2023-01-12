<?php
namespace MediaWiki\Skins\Vector\Components;

use MessageLocalizer;

/**
 * VectorComponentTableOfContentsContainer component
 */
class VectorComponentTableOfContentsContainer implements VectorComponent {
	/** @var string */
	private $id;
	/** @var MessageLocalizer */
	private $localizer;

	/**
	 * @param MessageLocalizer $localizer
	 * @param string $id (optional) of the pinnable element associated
	 *  with the pinnable header and container.
	 */
	public function __construct( MessageLocalizer $localizer, $id = 'vector-toc' ) {
		$this->localizer = $localizer;
		$this->id = $id;
	}

	/**
	 * In tableOfContents.js we have tableOfContents::getTableOfContentsSectionsData(),
	 * that yields the same result as this function, please make sure to keep them in sync.
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$pinnableHeader = new VectorComponentPinnableHeader(
			$this->localizer,
			true,
			$this->id,
			null,
			false,
			'h2'
		);
		$pinnableContainer = new VectorComponentPinnableContainer( $this->id );
		return $pinnableContainer->getTemplateData() + [
			'data-pinnable-header' => $pinnableHeader->getTemplateData(),
		];
	}
}
