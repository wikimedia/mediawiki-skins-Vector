<?php
namespace MediaWiki\Skins\Vector\Components;

use MessageLocalizer;
use User;

/**
 * VectorComponentMainMenu component
 */
class VectorComponentPageTools implements VectorComponent {

	/** @var array */
	private $menus;

	/** @var bool */
	private $isPinned;

	/** @var VectorComponentPinnableHeader|null */
	private $pinnableHeader;

	/** @var string */
	public const ID = 'vector-page-tools';

	/** @var string */
	public const TOOLBOX_ID = 'p-tb';

	/** @var string */
	private const ACTIONS_ID = 'p-cactions';

	/** @var MessageLocalizer */
	private $localizer;

	/**
	 * @param array $menus
	 * @param bool $isPinned
	 * @param MessageLocalizer $localizer
	 * @param User $user
	 */
	public function __construct(
		array $menus,
		bool $isPinned,
		MessageLocalizer $localizer,
		User $user
	) {
		$this->menus = $menus;
		$this->isPinned = $isPinned;
		$this->localizer = $localizer;
		$this->pinnableHeader = $user->isRegistered() ? new VectorComponentPinnableHeader(
			$localizer,
			$isPinned,
			// Name
			'vector-page-tools',
			// Feature name
			'page-tools-pinned'
		) : null;
	}

	/**
	 * Revises the labels of the p-tb and p-cactions menus.
	 *
	 * @return array
	 */
	private function getMenus(): array {
		return array_map( function ( $menu ) {
			switch ( $menu['id'] ?? '' ) {
				case self::TOOLBOX_ID:
					$menu['label'] = $this->localizer->msg( 'vector-page-tools-general-label' );
					break;
				case self::ACTIONS_ID:
					$menu['label'] = $this->localizer->msg( 'vector-page-tools-actions-label' );
					break;
			}

			return $menu;
		}, $this->menus );
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$pinnedContainer = new VectorComponentPinnedContainer( self::ID, $this->isPinned );
		$pinnableElement = new VectorComponentPinnableElement( self::ID );

		$data = $pinnableElement->getTemplateData() +
			$pinnedContainer->getTemplateData();

		return $data + [
			'data-pinnable-header' => $this->pinnableHeader ? $this->pinnableHeader->getTemplateData() : null,
			'data-menus' => $this->getMenus()
		];
	}
}
