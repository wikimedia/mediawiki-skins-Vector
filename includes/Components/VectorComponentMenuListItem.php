<?php
namespace MediaWiki\Skins\Vector\Components;

/**
 * VectorComponentMenuListItem component
 */
class VectorComponentMenuListItem implements VectorComponent {
	/** @var VectorComponentIconLink */
	private $link;
	/** @var string */
	private $class;
	/** @var string */
	private $id;

	/**
	 * @param VectorComponentIconLink $link
	 * @param string $class
	 * @param string $id
	 */
	public function __construct( VectorComponentIconLink $link, string $class = '', string $id = '' ) {
		$this->link = $link;
		$this->class = $class;
		$this->id = $id;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		return $this->link->getTemplateData() + [
			'item-class' => $this->class,
			'item-id' => $this->id,
		];
	}
}
