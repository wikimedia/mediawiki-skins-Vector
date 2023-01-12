<?php
namespace MediaWiki\Skins\Vector\Components;

use Config;
use MessageLocalizer;

/**
 * VectorComponentTableOfContents component
 */
class VectorComponentTableOfContents implements VectorComponent {

	/** @var array */
	private $tocData;

	/** @var MessageLocalizer */
	private $localizer;

	/** @var bool */
	private $isPinned;

	/** @var Config */
	private $config;

	/** @var VectorComponentPinnableHeader */
	private $pinnableHeader;

	 /** @var string */
	public const ID = 'vector-toc';

	/**
	 * @param array $tocData
	 * @param MessageLocalizer $localizer
	 * @param Config $config
	 */
	public function __construct(
		array $tocData,
		MessageLocalizer $localizer,
		Config $config
	) {
		$this->tocData = $tocData;
		$this->localizer = $localizer;
		// ToC is pinned by default, hardcoded for now
		$this->isPinned = true;
		$this->config = $config;
	}

	/**
	 * In tableOfContents.js we have tableOfContents::getTableOfContentsSectionsData(),
	 * that yields the same result as this function, please make sure to keep them in sync.
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$sections = $this->tocData[ 'array-sections' ] ?? [];
		if ( empty( $sections ) ) {
			return [];
		}
		// Populate button labels for collapsible TOC sections
		foreach ( $sections as &$section ) {
			if ( $section['is-top-level-section'] && $section['is-parent-section'] ) {
				$section['vector-button-label'] =
					$this->localizer->msg( 'vector-toc-toggle-button-label', $section['line'] )->text();
			}
		}

		$pinnableElement = new VectorComponentPinnableElement( self::ID );

		return $pinnableElement->getTemplateData() +
			array_merge( $this->tocData, [
			'is-vector-toc-beginning-enabled' => $this->config->get(
				'VectorTableOfContentsBeginning'
			),
			'vector-is-collapse-sections-enabled' =>
				$this->tocData[ 'number-section-count'] >= $this->config->get(
					'VectorTableOfContentsCollapseAtCount'
				),
		] );
	}
}
