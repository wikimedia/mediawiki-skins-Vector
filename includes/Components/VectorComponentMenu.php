<?php
namespace MediaWiki\Skins\Vector\Components;

use Countable;

/**
 * VectorComponentMenu component
 */
class VectorComponentMenu implements VectorComponent, Countable {
	public const BUTTON_CLASSES = 'cdx-button cdx-button--fake-button '
	. 'cdx-button--fake-button--enabled cdx-button--weight-quiet';
	public const ICON_ONLY_BUTTON_CLASS = 'cdx-button--icon-only';
	// TODO: Remove user-links-collapsible-item after I12cdb5c2a3dff638d59066b2c2c9597133855dee is in prod for 2 weeks
	public const COLLAPSIBLE_CLASS = 'user-links-collapsible-item vector-menu-item--collapsible';

	/**
	 * @param array $data menu data
	 * @param array $menuItemStyles all menu items will use default styles unless there's an item-specific override
	 * @param array $menuItemStyleOverrides styles for individual menu items keyed by item id
	 */
	public function __construct(
		private array $data,
		private array $menuItemStyles = [],
		private array $menuItemStyleOverrides = []
	) {
		$this->data += [
			'class' => '',
			'label' => '',
			'html-tooltip' => '',
			'label-class' => '',
			'html-before-portal' => '',
			'html-items' => '',
			'html-after-portal' => '',
			'array-list-items' => null,
		];

		$menuItemsData = $this->data['array-list-items'];
		if ( $this->data[ 'html-items' ] ) {
			// Using HTML string rendering
			$this->data[ 'array-list-items' ] = null;
		} elseif ( $menuItemsData ) {
			// Using template based rendering, update the menu item styles
			$this->data['array-list-items'] = $this->updateMenuItemStyles(
				$menuItemsData,
				$menuItemStyles,
				$menuItemStyleOverrides
			);
		}
	}

	/**
	 * Counts how many items the menu has.
	 */
	public function count(): int {
		$items = $this->data['array-list-items'] ?? null;
		if ( $items ) {
			return count( $items );
		}
		$htmlItems = $this->data['html-items'] ?? '';
		return substr_count( $htmlItems, '<li' );
	}

	/**
	 * Update menu item styling based of default menu styles and overrides
	 * Style options include: 'button', 'collapsible', 'icon', 'class'
	 * 'button' can be boolean or an array with button options, e.g. ['iconOnly' => true]
	 *
	 * @param array $menuItems
	 * @param array $menuItemStyles all menu items will use these styles unless there's an item specific override
	 * @param array $menuItemStyleOverrides styles for individual menu items keyed by item id
	 * @return array
	 */
	private static function updateMenuItemStyles( $menuItems, $menuItemStyles, $menuItemStyleOverrides ) {
		return array_map( static function ( $item ) use ( $menuItemStyles, $menuItemStyleOverrides ) {
			$id = $item['id'];
			$hasOverrides = $id && isset( $menuItemStyleOverrides[ $id ] );
			$styles = $hasOverrides ? $menuItemStyleOverrides[ $id ] : $menuItemStyles;

			$isCollapsible = $styles['collapsible'] ?? false;
			// collapsible class is added to the item (LI element) class
			if ( $isCollapsible ) {
				$class = $item['class'] ?? '';
				$item['class'] = $class . ' ' . self::COLLAPSIBLE_CLASS;
			}

			$customClass = $menuItemStyleOverrides[ $id ]['class'] ?? $menuItemStyles['class'] ?? '';
			if ( $customClass ) {
				$item['class'] = trim( $item['class'] . ' ' . $customClass );
			}

			// Update link classes
			$item['array-links'] = array_map( static function ( $link ) use ( $styles ) {
				if ( array_key_exists( 'icon', $styles ) ) {
					$link['icon'] = $styles['icon'];
				}
				$link['array-attributes'] = array_map( static function ( $attribute ) use ( $styles ) {
					if ( $attribute['key'] === 'class' ) {
						$newClass = $attribute['value'];
						$isButton = $styles['button'] ?? false;
						$isIconOnlyButton = $styles['button' ]['iconOnly'] ?? false;
						if ( $isButton ) {
							$newClass .= ' ' . self::BUTTON_CLASSES;
						}
						if ( $isIconOnlyButton ) {
							$newClass .= ' ' . self::ICON_ONLY_BUTTON_CLASS;
						}
						$attribute['value'] = $newClass;
					}
					return $attribute;
				}, $link['array-attributes'] ?? [] );
				return $link;
			}, $item['array-links'] ?? [] );
			return $item;
		}, $menuItems );
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		return $this->data;
	}
}
