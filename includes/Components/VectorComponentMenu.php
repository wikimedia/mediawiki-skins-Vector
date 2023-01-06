<?php
namespace MediaWiki\Skins\Vector\Components;

use Countable;

/**
 * VectorComponentMenu component
 */
class VectorComponentMenu implements VectorComponent, Countable {
	/** @var array */
	private $data;
	/** @var array */
	private $items;

	/**
	 * @param array $data
	 * @param VectorComponentMenuListItem[] $items
	 */
	public function __construct( array $data, array $items = [] ) {
		$this->data = $data;
		$this->items = $items;
	}

	/**
	 * Counts how many items the menu has.
	 *
	 * @return int
	 */
	public function count(): int {
		$htmlItems = $this->data['html-items'] ?? '';
		return substr_count( $htmlItems, '<li' );
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$dataItems = [];
		foreach ( $this->items as $item ) {
			$dataItems[] = $item->getTemplateData();
		}
		return $this->data + [
			'class' => '',
			'label' => '',
			'html-tooltip' => '',
			'label-class' => '',
			'heading-class' => '',
			'html-before-portal' => '',
			'html-items' => '',
			'html-after-portal' => '',
			'data-items' => $dataItems,
		];
	}
}
