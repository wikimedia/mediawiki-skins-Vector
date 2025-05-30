<?php
namespace MediaWiki\Skins\Vector\Components;

use MediaWiki\Skin\Skin;
use MediaWiki\Skins\Vector\Constants;
use MediaWiki\Skins\Vector\FeatureManagement\FeatureManager;
use MediaWiki\User\UserIdentity;
use MessageLocalizer;

/**
 * VectorComponentMainMenu component
 */
class VectorComponentMainMenu implements VectorComponent {
	/** @var VectorComponent|null */
	private $optOut;
	/** @var array */
	private $sidebarData;
	/** @var array */
	private $languageData;
	/** @var MessageLocalizer */
	private $localizer;
	/** @var bool */
	private $isPinned;
	/** @var VectorComponentPinnableHeader|null */
	private $pinnableHeader;
	/** @var string */
	public const ID = 'vector-main-menu';

	/**
	 * @param array $sidebarData
	 * @param array $languageData
	 * @param MessageLocalizer $localizer
	 * @param UserIdentity $user
	 * @param FeatureManager $featureManager
	 * @param Skin $skin
	 */
	public function __construct(
		array $sidebarData,
		array $languageData,
		MessageLocalizer $localizer,
		UserIdentity $user,
		FeatureManager $featureManager,
		Skin $skin
	) {
		$this->sidebarData = $sidebarData;
		$this->languageData = $languageData;
		$this->localizer = $localizer;
		$this->isPinned = $featureManager->isFeatureEnabled( Constants::FEATURE_MAIN_MENU_PINNED );

		$this->pinnableHeader = new VectorComponentPinnableHeader(
			$this->localizer,
			$this->isPinned,
			self::ID,
			'main-menu-pinned'
		);

		if ( $user->isRegistered() ) {
			$this->optOut = new VectorComponentMainMenuActionOptOut( $skin );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$action = $this->optOut;
		$pinnableHeader = $this->pinnableHeader;

		$portletsRest = [];
		foreach ( $this->sidebarData[ 'array-portlets-rest' ] as $data ) {
			$portletsRest[] = ( new VectorComponentMenu( $data ) )->getTemplateData();
		}
		$firstPortlet = new VectorComponentMenu( $this->sidebarData['data-portlets-first'] );
		$languageMenu = new VectorComponentMenu( $this->languageData );

		$pinnableContainer = new VectorComponentPinnableContainer( self::ID, $this->isPinned );
		$pinnableElement = new VectorComponentPinnableElement( self::ID );

		return $pinnableElement->getTemplateData() + $pinnableContainer->getTemplateData() + [
			'data-portlets-first' => $firstPortlet->getTemplateData(),
			'array-portlets-rest' => $portletsRest,
			'data-main-menu-action' => $action ? $action->getTemplateData() : null,
			'data-pinnable-header' => $pinnableHeader ? $pinnableHeader->getTemplateData() : null,
			'data-languages' => $languageMenu->getTemplateData(),
		];
	}
}
