<?php
namespace MediaWiki\Skins\Vector\Components;

/**
 * VectorComponentMenu component
 */
class VectorComponentMenu implements VectorComponent {
	/** @var array */
	private $data;

	/**
	 * @param array $data
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		return $this->data + [
			'class' => '',
			'label' => '',
			'html-tooltip' => '',
			'label-class' => '',
			'heading-class' => '',
			'html-before-portal' => '',
			'html-items' => '',
			'html-after-portal' => '',
		];
	}
}
