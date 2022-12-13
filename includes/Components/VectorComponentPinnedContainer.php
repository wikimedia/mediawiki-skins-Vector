<?php
namespace MediaWiki\Skins\Vector\Components;

/**
 * VectorComponentPinnedContainer component
 * To be used with PinnedContainer or UnpinnedContainer templates.
 */
class VectorComponentPinnedContainer implements VectorComponent {
	/** @var string */
	private $id;
	/** @var bool */
	private $isPinned;

	/**
	 * @param string $id
	 * @param bool $isPinned
	 */
	public function __construct( string $id, bool $isPinned = true ) {
		$this->id = $id;
		$this->isPinned = $isPinned;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		return [
			'id' => $this->id,
			'is-pinned' => $this->isPinned,
		];
	}
}
