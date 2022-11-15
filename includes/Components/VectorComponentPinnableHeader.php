<?php
namespace MediaWiki\Skins\Vector\Components;

use MessageLocalizer;

/**
 * VectorComponentPinnableHeader component
 */
class VectorComponentPinnableHeader implements VectorComponent {
	/** @var MessageLocalizer */
	private $localizer;
	/** @var bool */
	private $pinned;
	/** @var string */
	private $name;
	/** @var bool */
	private $moveElement;

	/**
	 * @param MessageLocalizer $localizer
	 * @param bool $pinned
	 * @param string $name
	 * @param bool|null $moveElement
	 */
	public function __construct(
		MessageLocalizer $localizer,
		bool $pinned,
		string $name,
		?bool $moveElement = true
	) {
		$this->localizer = $localizer;
		$this->pinned = $pinned;
		$this->name = $name;
		$this->moveElement = $moveElement;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$messageLocalizer = $this->localizer;
		$data = [
			'is-pinned' => $this->pinned,
			'label' => $messageLocalizer->msg( $this->name . '-label' ),
			'pin-label' => $messageLocalizer->msg( 'vector-pin-element-label' ),
			'unpin-label' => $messageLocalizer->msg( 'vector-unpin-element-label' ),
			'data-name' => $this->name
		];
		if ( $this->moveElement ) {
			// Assumes consistent naming standard for pinnable elements and their containers
			$data = array_merge( $data, [
				'data-pinnable-element-id' => $this->name . '-content',
				'data-unpinned-container-id' => $this->name . '-content-container',
				'data-pinned-container-id' => $this->name . '-pinned-container',
			] );
		}
		return $data;
	}
}
