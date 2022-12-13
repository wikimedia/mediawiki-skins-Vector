<?php
namespace MediaWiki\Skins\Vector\Components;

use Skin;

/**
 * VectorComponentMainMenu component
 */
class VectorComponentPageTools implements VectorComponent {

	/** @var array */
	private $toolbox;

	/** @var array */
	private $actionsMenu;

	/** @var bool */
	private $isPinned;

	/** @var Skin */
	private $skin;

	/** @var VectorComponentPinnableHeader|null */
	private $pinnableHeader;

	/** @var string */
	public const TOOLBOX_ID = 'p-tb';

	/**
	 * @param array $toolbox
	 * @param array $actionsMenu
	 * @param bool $isPinned
	 * @param Skin $skin
	 */
	public function __construct(
		array $toolbox,
		array $actionsMenu,
		bool $isPinned,
		Skin $skin
	) {
		$user = $skin->getUser();
		$this->toolbox = $toolbox;
		$this->actionsMenu = $actionsMenu;
		$this->isPinned = $isPinned;
		$this->pinnableHeader = $user->isRegistered() ? new VectorComponentPinnableHeader(
			$skin->getContext(),
			$isPinned,
			// Name
			'vector-page-tools',
			// Feature name
			'page-tools-pinned'
		) : null;
		$this->skin = $skin;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$toolbox = new VectorComponentMenu( $this->toolbox );
		$actions = new VectorComponentMenu( $this->actionsMenu );
		$id = 'vector-page-tools';
		$pinnedContainer = new VectorComponentPinnedContainer( $id, $this->isPinned );
		$pinnableElement = new VectorComponentPinnableElement( $id );

		$data = $pinnableElement->getTemplateData() +
			$pinnedContainer->getTemplateData();
		return $data + [
			'data-pinnable-header' => $this->pinnableHeader ? $this->pinnableHeader->getTemplateData() : null,
			'data-menus' => [
				$toolbox->getTemplateData(),
				$actions->getTemplateData(),
			]
		];
	}
}
