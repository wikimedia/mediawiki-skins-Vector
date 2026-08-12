<?php
namespace MediaWiki\Skins\Vector\Components;

use MediaWiki\Language\MessageLocalizer;
use MediaWiki\Skins\Vector\Constants;
use MediaWiki\Skins\Vector\FeatureManagement\FeatureManager;

/**
 * VectorComponentPageTools component
 */
class VectorComponentPageTools implements VectorComponent {

	private readonly bool $isPinned;
	private readonly VectorComponentPinnableHeader $pinnableHeader;

	/** @var string */
	public const ID = 'vector-page-tools';

	/** @var string */
	public const TOOLBOX_ID = 'p-tb';

	/** @var string */
	private const ACTIONS_ID = 'p-cactions';

	/** @var string */
	private const VIEWS_ID = 'p-views';

	/**
	 * @param array $menus
	 * @param MessageLocalizer $localizer
	 * @param FeatureManager $featureManager
	 * @param bool $hasCollapsibleElements whether the more menu contains collapsible elements
	 *  that are revealed at lower resolutions
	 */
	public function __construct(
		private readonly array $menus,
		private readonly MessageLocalizer $localizer,
		FeatureManager $featureManager,
		private readonly bool $hasCollapsibleElements = false
	) {
		$this->isPinned = $featureManager->isFeatureEnabled( Constants::FEATURE_PAGE_TOOLS_PINNED );
		$this->pinnableHeader = new VectorComponentPinnableHeader(
			$localizer,
			$this->isPinned,
			// Name
			self::ID,
			// Feature name
			'page-tools-pinned',
			'vector-unpin-element-aria-label',
			'vector-pin-element-aria-label'
		);
	}

	/**
	 * Revise the p-tb, p-cactions, and p-views menus.
	 */
	private function getMenus(): array {
		$pageToolsMenus = [];
		$viewsMenuData = [];
		$collapsibleClass = $this->hasCollapsibleElements ? ' vector-has-collapsible-items' : '';
		foreach ( $this->menus as $menu ) {
			switch ( $menu['id'] ?? '' ) {
				case self::TOOLBOX_ID:
					// Update the label.
					$menu['label'] = $this->localizer->msg( 'vector-page-tools-general-label' )->text();
					$pageToolsMenus[] = $menu;
					break;
				case self::ACTIONS_ID:
					$menuComponent = new VectorComponentMenu( [
						'id' => $menu['id'],
						'class' => ( $menu['class'] ?? '' ) .
							// [T432149] This ensures that views is displayed on lower resolutions
							// (even if .emptyPortlet present). Do not remove!
							$collapsibleClass,
						'label' => $this->localizer->msg( 'vector-page-tools-actions-label' )->text(),
						'array-list-items' => $menu['array-items'],
						'html-after-portal' => $menu['html-after-portal'] ?? '',
					] );
					$pageToolsMenus[] = $menuComponent->getTemplateData();
					break;
				case self::VIEWS_ID:
					$menuComponent = new VectorComponentMenu( [
						'id' => $menu['id'],
						'class' => $menu['class'] ?? '',
						'array-list-items' => $menu['array-items'],
					], [
						'collapsible' => true
					] );
					$viewsMenuData = $menuComponent->getTemplateData();
					break;
				default:
					if ( isset( $menu['id'] ) ) {
						$pageToolsMenus[] = $menu;
					}
					break;
			}
		}
		// Combine views and actions menus
		$pageToolsMenus[0]['array-list-items'] = array_merge(
			$viewsMenuData['array-list-items'] ?? [],
			$pageToolsMenus[0]['array-list-items'] ?? []
		);
		return $pageToolsMenus;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$pinnedContainer = new VectorComponentPinnableContainer( self::ID, $this->isPinned );
		$pinnableElement = new VectorComponentPinnableElement( self::ID );

		$data = $pinnableElement->getTemplateData() +
			$pinnedContainer->getTemplateData();

		return $data + [
			'data-pinnable-header' => $this->pinnableHeader->getTemplateData(),
			'data-menus' => $this->getMenus()
		];
	}
}
