<?php
namespace MediaWiki\Skins\Vector\Components;

use Skin;

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

	/**
	 * @param array $sidebarData
	 * @param Skin $skin
	 * @param bool $shouldLanguageAlertBeInSidebar
	 */
	public function __construct(
		array $sidebarData,
		Skin $skin,
		bool $shouldLanguageAlertBeInSidebar
	) {
		$this->sidebarData = $sidebarData;
		$user = $skin->getUser();
		if ( $user->isRegistered() ) {
			$this->optOut = new VectorComponentMainMenuActionOptOut( $skin );
		}
		if ( $shouldLanguageAlertBeInSidebar ) {
			$this->alert = new VectorComponentMainMenuActionLanguageSwitchAlert( $skin );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$action = $this->optOut;
		$alert = $this->alert;

		return $this->sidebarData + [
			'data-main-menu-action' => $action ? $action->getTemplateData() : null,
			// T295555 Add language switch alert message temporarily (to be removed).
			'data-vector-language-switch-alert' => $alert ? $alert->getTemplateData() : null,
		];
	}
}
