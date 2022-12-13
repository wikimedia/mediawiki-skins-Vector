<?php
namespace MediaWiki\Skins\Vector\Components;

use MessageLocalizer;
use Skin;
use User;

/**
 * VectorComponentMainMenu component
 */
class VectorComponentMainMenu implements VectorComponent {
	/** @var VectorComponent|null */
	private $optOut;
	/** @var VectorComponent|null */
	private $alert;
	/** @var array */
	private $sidebarData;
	/** @var array */
	private $languageData;
	/** @var MessageLocalizer */
	private $localizer;
	/** @var User */
	private $user;

	/**
	 * @param array $sidebarData
	 * @param Skin $skin
	 * @param bool $shouldLanguageAlertBeInSidebar
	 * @param array $languageData
	 */
	public function __construct(
		array $sidebarData,
		Skin $skin,
		bool $shouldLanguageAlertBeInSidebar,
		array $languageData
	) {
		$this->sidebarData = $sidebarData;
		$this->localizer = $skin->getContext();
		$this->languageData = $languageData;
		$user = $skin->getUser();
		$this->user = $user;
		if ( $user->isRegistered() ) {
			$this->optOut = new VectorComponentMainMenuActionOptOut( $skin );
		}
		if ( $shouldLanguageAlertBeInSidebar ) {
			$this->alert = new VectorComponentMainMenuActionLanguageSwitchAlert( $skin );
		}
	}

	/**
	 * @return User
	 */
	private function getUser(): User {
		return $this->user;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$action = $this->optOut;
		$alert = $this->alert;

		$id = 'vector-main-menu';
		$pinnableHeader = new VectorComponentPinnableHeader(
			$this->localizer,
			false,
			$id,
			null
		);

		$portletsRest = [];
		foreach ( $this->sidebarData[ 'array-portlets-rest' ] as $data ) {
			$portletsRest[] = ( new VectorComponentMenu( $data ) )->getTemplateData();
		}
		$firstPortlet = new VectorComponentMenu( $this->sidebarData['data-portlets-first'] );
		$languageMenu = new VectorComponentMenu( $this->languageData );
		return [
			'data-portlets-first' => $firstPortlet->getTemplateData(),
			'array-portlets-rest' => $portletsRest,
			'data-main-menu-action' => $action ? $action->getTemplateData() : null,
			// T295555 Add language switch alert message temporarily (to be removed).
			'data-vector-language-switch-alert' => $alert ? $alert->getTemplateData() : null,
			'data-pinnable-header' => $pinnableHeader->getTemplateData(),
			'data-languages' => $languageMenu->getTemplateData(),
		];
	}
}
