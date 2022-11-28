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
	/**
	 * @var bool
	 * Flag controlling if the pinnable element should be automatically moved in the DOM when pinned/unpinned
	 */
	private $moveElement;
	/**
	 * @var string
	 */
	private $labelTagName;

	/**
	 * @param MessageLocalizer $localizer
	 * @param bool $pinned
	 * @param string $name
	 * @param bool|null $moveElement
	 * @param string|null $labelTagName Element type of the label. Either a 'div' or a 'h2'
	 *   in the case of the pinnable ToC.
	 */
	public function __construct(
		MessageLocalizer $localizer,
		bool $pinned,
		string $name,
		?bool $moveElement = true,
		?string $labelTagName = 'div'
	) {
		$this->localizer = $localizer;
		$this->pinned = $pinned;
		$this->name = $name;
		$this->moveElement = $moveElement;
		$this->labelTagName = $labelTagName;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$messageLocalizer = $this->localizer;
		$data = [
			'is-pinned' => $this->pinned,
			'label' => $messageLocalizer->msg( $this->name . '-label' ),
			'label-tag-name' => $this->labelTagName,
			'pin-label' => $messageLocalizer->msg( 'vector-pin-element-label' ),
			'unpin-label' => $messageLocalizer->msg( 'vector-unpin-element-label' ),
			'data-name' => $this->name
		];
		if ( $this->moveElement ) {
			// Assumes consistent naming standard for pinnable elements and their containers
			$data = array_merge( $data, [
				'data-pinnable-element-id' => $this->name . '-pinnable-element',
				'data-unpinned-container-id' => $this->name . '-unpinned-container',
				'data-pinned-container-id' => $this->name . '-pinned-container',
			] );
		}
		return $data;
	}
}
