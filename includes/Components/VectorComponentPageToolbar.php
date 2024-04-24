<?php
namespace MediaWiki\Skins\Vector\Components;

use MediaWiki\Language\MessageLocalizer;
use MediaWiki\Message\Message;
use MediaWiki\Skins\Vector\FeatureManagement\FeatureManager;

/**
 * VectorPageActions component
 */
class VectorComponentPageToolbar implements VectorComponent {
	private const ICON_BUTTON = [
		'class' => '',
		'button' => [
			'action' => 'progressive'
		],
		'collapsible' => true,
	];
	private const ICON_ONLY_BUTTON = [
		'class' => '',
		'button' => [
			'iconOnly' => true,
		],
		'collapsible' => true,
	];

	public function __construct(
		private readonly MessageLocalizer $localizer,
		private readonly FeatureManager $featureManager,
		private array $portletData,
		private array $sidebar,
		private readonly bool $hasAddTopicButton = false
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
	 * Creates a duplicate of the views menu that will be injected page tools dropdown
	 * This menu will only be shown at low resolutions (when the `views` menu is hidden).
	 *
	 * @param array $viewsData
	 * @return array
	 */
	private function createViewsMoreMenu( array $viewsData ): array {
		$viewsMoreMenu = $viewsData;
		foreach ( $viewsMoreMenu[ 'array-items' ] ?? [] as $key => $item ) {
			$item[ 'id' ] .= '-more';
			$viewsMoreMenu[ 'array-items' ][ $key ] = $item;
		}
		return $viewsMoreMenu;
	}

	/**
	 * Promote watch link from actions to views and add an icon
	 *
	 * @param array &$viewsData
	 * @param array &$actionsData
	 */
	private static function moveWatchLinkToViews( array &$viewsData, array &$actionsData ): void {
		foreach ( $actionsData[ 'array-items' ] ?? [] as $action ) {
			if ( $action[ 'name' ] === 'watch' || $action[ 'name' ] === 'unwatch' ) {
				// Insert after history
				$historyId = array_search( 'history', array_column( $viewsData[ 'array-items' ] ?? [], 'name' ) );
				if ( $historyId ) {
					array_splice( $viewsData[ 'array-items' ], $historyId + 1, 0, [ $action ] );
					// Remove from actions
					$actionId = array_search( $action['name'], array_column( $actionsData[ 'array-items' ], 'name' ) );
					array_splice( $actionsData[ 'array-items' ], $actionId, 1 );
				}
			}
		}
	}

	/**
	 * Pulls the page tools menu out of $sidebar into $pageToolsMenu
	 *
	 * @param array &$sidebar
	 * @param array &$pageToolsMenu
	 */
	private static function extractToolboxFromSidebar( array &$sidebar, array &$pageToolsMenu ) {
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
	 *
	 * If present the add section item is removed as it
	 * is being rendered outside the component.
	 *
	 * @param array $viewsData
	 * @return array
	 */
	private function getToolbarActions( array $viewsData ): array {
		if ( !$viewsData ) {
			return [];
		}
		$actionsMenu = new VectorComponentMenu(
			[
				'id' => $viewsData['id'] ?? 'p-views',
				'class' => $viewsData['class'] ?? '',
				'label' => null,
				'html-items' => null,
				'array-list-items' => $viewsData['array-items'],
			],
			[
				'class' => 'vector-tab-noicon',
				'collapsible' => true,
				'icon' => false,
			],
			[
				'ca-addsection' => $this->hasAddTopicButton ? false : null,
				'ca-unwatch' => self::ICON_BUTTON,
				'ca-watch' => self::ICON_BUTTON,
				'ca-wikilove' => self::ICON_ONLY_BUTTON,
				'ca-bookmark' => self::ICON_ONLY_BUTTON,
			]
		);
		return $actionsMenu->getTemplateData();
	}

	private function getAssociatedPages(): array {
		$associatedPages = $this->portletData['data-associated-pages'] ?? [];
		$associatedPagesMenu = new VectorComponentMenu(
			[
				'id' => $associatedPages['id'] ?? 'p-associated-pages',
				'class' => $associatedPages['class'] ?? '',
				'html-items' => null,
				'array-list-items' => $associatedPages['array-items'] ?? [],
			],
			[
				'class' => 'vector-tab-noicon',
			]
		);
		return $associatedPagesMenu->getTemplateData();
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$viewsData = $this->portletData['data-views'] ?? [];
		$actionsData = $this->portletData['data-actions'] ?? [];
		$toolbarData = [];
		self::extractToolboxFromSidebar( $this->sidebar, $toolbarData );
		self::moveWatchLinkToViews( $viewsData, $actionsData );
		$viewsMoreData = self::createViewsMoreMenu( $viewsData );

		$toolsDropdown = new VectorComponentDropdown(
			VectorComponentPageTools::ID . '-dropdown',
			$this->msg( 'toolbox' )->text(),
			VectorComponentPageTools::ID . '-dropdown',
			'verticalEllipsis'
		);
		$pageToolsMenu = new VectorComponentPageTools(
			array_merge( [ $viewsMoreData ], [ $actionsData ], $toolbarData ),
			$this->localizer,
			$this->featureManager
		);
		return [
			'data-associated-pages' => $this->getAssociatedPages(),
			'data-toolbar-actions' => $this->getToolbarActions( $viewsData ),
			'data-page-tools' => $pageToolsMenu->getTemplateData(),
			'data-page-tools-dropdown' => $toolsDropdown->getTemplateData(),
		];
	}
}
