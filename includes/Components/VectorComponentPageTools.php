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

	/** @var Skin */
	private $skin;

	/** @var VectorComponentPinnableHeader */
	private $pinnableHeader;

	/** @var string */
	public const TOOLBOX_ID = 'p-tb';

	/**
	 * @param array $toolbox
	 * @param array $actionsMenu
	 * @param VectorComponentPinnableHeader $pinnableHeader
	 * @param Skin $skin
	 */
	public function __construct(
		array $toolbox,
		array $actionsMenu,
		VectorComponentPinnableHeader $pinnableHeader,
		Skin $skin
	) {
		$this->toolbox = $toolbox;
		$this->actionsMenu = $actionsMenu;
		$this->pinnableHeader = $pinnableHeader;
		$this->skin = $skin;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$menusData = [ $this->toolbox, $this->actionsMenu ];

		$pinnableDropdownData = [
			'id' => 'vector-page-tools',
			'class' => 'vector-page-tools',
			'label' => $this->skin->msg( 'toolbox' ),
			'has-multiple-menus' => true,
			'data-pinnable-header' => $this->pinnableHeader->getTemplateData(),
			'data-menus' => $menusData
		];
		return $pinnableDropdownData;
	}
}
