<?php
namespace MediaWiki\Skins\Vector\Components;

use MediaWiki\Language\MessageLocalizer;
use MediaWiki\Message\Message;
use MediaWiki\Skins\Vector\FeatureManagement\FeatureManager;

/**
 * VectorPageActions component
 */
class VectorComponentPageToolbar implements VectorComponent {
	private const ICON_LINK = [];
	private const ICON_ONLY_BUTTON = [
		'class' => '',
		'button' => [
			'iconOnly' => true,
		],
	];

	public function __construct(
		private readonly MessageLocalizer $localizer,
		private readonly FeatureManager $featureManager,
		private readonly array $portletData,
		private readonly array $sidebar
	) {
	}

	/**
	 * @param string $key
	 * @return Message
	 */
	private function msg( $key ): Message {
		return $this->localizer->msg( $key );
	}

	/**
	 * Pulls the page tools menu out of $sidebar into $pageToolsMenu
	 *
	 * @param array &$sidebar
	 * @param array &$pageToolsMenu
	 */
	private static function extractPageToolsFromSidebar( array &$sidebar, array &$pageToolsMenu ) {
		$restPortlets = $sidebar[ 'array-portlets-rest' ] ?? [];
		$toolboxMenuIndex = array_search(
			VectorComponentPageTools::TOOLBOX_ID,
			array_column(
				$restPortlets,
				'id'
			)
		);

		if ( $toolboxMenuIndex !== false ) {
			// Splice removes the toolbox menu from the $restPortlets array
			// and current returns the first value of array_splice, i.e. the $toolbox menu data.
			$pageToolsMenu = array_splice( $restPortlets, $toolboxMenuIndex );
			$sidebar['array-portlets-rest'] = $restPortlets;
		}
	}

	/**
	 * Creates a toolbar actions menu using data-views
	 * ensuring only watch, wikilove and bookmark appear
	 * with icons.
	 */
	private function getToolbarActions(): array {
		$views = $this->portletData['data-views'] ?? [];
		if ( !$views ) {
			return [];
		}
		$actionsMenu = new VectorComponentMenu(
			[
				'id' => $views['id'] ?? 'p-views',
				'class' => $views['class'] ?? '',
				'label' => null,
				'html-items' => null,
				'array-list-items' => $views['array-items'],
			],
			[
				'class' => 'vector-tab-noicon',
				'collapsible' => true,
				'icon' => false,
			],
			[
				'ca-unwatch' => self::ICON_LINK,
				'ca-watch' => self::ICON_LINK,
				'ca-wikilove' => self::ICON_ONLY_BUTTON,
				'ca-bookmark' => self::ICON_ONLY_BUTTON,
			]
		);
		return $actionsMenu->getTemplateData();
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$portlets = $this->portletData;
		$sidebar = $this->sidebar;
		$pageToolsMenu = [];
		self::extractPageToolsFromSidebar( $sidebar, $pageToolsMenu );
		$toolsDropdown = new VectorComponentDropdown(
			VectorComponentPageTools::ID . '-dropdown',
			$this->msg( 'toolbox' )->text(),
			VectorComponentPageTools::ID . '-dropdown',
		);
		$pageToolsMenu = new VectorComponentPageTools(
			array_merge( [ $portlets['data-actions'] ?? [] ], $pageToolsMenu ),
			$this->localizer,
			$this->featureManager
		);
		return [
			'data-toolbar-actions' => $this->getToolbarActions(),
			'data-page-tools' => $pageToolsMenu->getTemplateData(),
			'data-portlets' => $portlets,
			'data-page-tools-dropdown' => $toolsDropdown->getTemplateData(),
		];
	}
}
