<?php
namespace MediaWiki\Skins\Vector\Components;

use MediaWiki\Html\Html;
use MediaWiki\Language\MessageLocalizer;
use MediaWiki\Linker\Linker;
use MediaWiki\Message\Message;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserNameUtils;

/**
 * VectorComponentUserLinks component
 */
class VectorComponentUserLinks implements VectorComponent {
	// TODO: Remove user-links-collapsible-item after I12cdb5c2a3dff638d59066b2c2c9597133855dee is in prod for 2 weeks
	private const COLLAPSIBLE_CLASS = 'vector-user-links-dropdown--collapsible user-links-collapsible-item';
	private const ACCOUNT_MENU_ITEM_KEYS = [
		'createaccount',
		'login',
		'login-private',
	];
	private const OVERFLOW_MENU_ITEM_KEYS = [
		'readinglists',
		'watchlist',
		'sitesupport',
	];

	/**
	 * @param MessageLocalizer $localizer
	 * @param UserIdentity $user
	 * @param UserNameUtils $userNameUtils
	 * @param array $portletData
	 * @param string $userIcon that represents the current type of user
	 */
	public function __construct(
		private readonly MessageLocalizer $localizer,
		private readonly UserIdentity $user,
		private readonly UserNameUtils $userNameUtils,
		private readonly array $portletData,
		private readonly string $userIcon = 'userAvatar',
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
	 * @param bool $isDefaultAnonUserLinks
	 * @param int $userLinksCount
	 * @return VectorComponentDropdown
	 */
	private function getDropdown( $isDefaultAnonUserLinks, $userLinksCount ) {
		$user = $this->user;
		$isAnon = !$user->isRegistered();

		$class = 'vector-user-menu';
		$class .= ' vector-button-flush-right';
		$class .= !$isAnon ?
			' vector-user-menu-logged-in' :
			' vector-user-menu-logged-out';

		// Hide entire user links dropdown on larger viewports if it only contains
		// create account & login link, which are only shown on smaller viewports
		if ( $isAnon && $isDefaultAnonUserLinks ) {
			$linkclass = ' ' . self::COLLAPSIBLE_CLASS;

			if ( $userLinksCount === 0 ) {
				// The user links can be completely empty when even login is not possible
				// (e.g using remote authentication). In this case, we need to hide the
				// dropdown completely not only on larger viewports.
				$linkclass .= '--none';
			}

			$class .= $linkclass;
		}

		$tooltip = Html::expandAttributes( [
			'title' => $this->msg( 'vector-personal-tools-tooltip' )->text(),
		] );
		$icon = $this->userIcon;
		if ( $icon === '' && $userLinksCount ) {
			$icon = 'ellipsis';
			// T287494 We use tooltip messages to provide title attributes on hover over certain menu icons.
			// For modern Vector, the "tooltip-p-personal" key is set to "User menu" which is appropriate for
			// the user icon (dropdown indicator for user links menu) for logged-in users.
			// This overrides the tooltip for the user links menu icon which is an ellipsis for anonymous users.
			$tooltip = Linker::tooltip( 'vector-anon-user-menu-title' ) ?? '';
		}

		return new VectorComponentDropdown(
			'vector-user-links-dropdown', $this->msg( 'personaltools' )->text(), $class, $icon, $tooltip
		);
	}

	/**
	 * @param UserIdentity $user
	 * @return array
	 */
	private function getOverflowKeys( $user ) {
		// Only certain items get promoted to the overflow menu:
		// * readinglist
		// * watchlist
		// * (account keys)
		if ( $this->userNameUtils->isTemp( $user->getName() ) ) {
			// Temporary accounts don't show the account items in overflow
			return array_diff(
				self::OVERFLOW_MENU_ITEM_KEYS,
				self::ACCOUNT_MENU_ITEM_KEYS
			);
		} else {
			return array_merge( self::OVERFLOW_MENU_ITEM_KEYS, self::ACCOUNT_MENU_ITEM_KEYS );
		}
	}

	/**
	 * @return array
	 */
	private function getMenus() {
		$user = $this->user;
		$portletData = $this->portletData;
		$overflowKeys = self::getOverflowKeys( $user );
		$userMenuData = $portletData[ 'data-user-menu' ][ 'array-items' ];
		$userMenuOverrides = [];
		// Construct overrides for any menu item that is duplicated in the overflow menu
		foreach ( $overflowKeys as $key ) {
			$menuItem = array_filter( $userMenuData, static function ( $item ) use ( $key ) {
				return $item['name'] === $key;
			} );

			if ( $menuItem ) {
				$menuItem = array_values( $menuItem )[0];
				$userMenuOverrides[ $menuItem[ 'id' ] ] = [ 'collapsible' => true ];
			}
		}

		return [
			new VectorComponentMenu( [
				'id' => 'p-personal',
				'label' => null,
				'class' => $portletData[ 'data-user-menu' ][ 'class' ],
				'html-tooltip' => $portletData[ 'data-user-menu' ][ 'html-tooltip' ] ?? '',
				'array-list-items' => $userMenuData
			], [], $userMenuOverrides )
		];
	}

	/**
	 * What class should the overflow menu have?
	 *
	 * @param array $arrayListItems
	 * @return string
	 */
	private static function getOverflowMenuClass( $arrayListItems ) {
		$overflowMenuClass = 'mw-portlet';
		if ( count( $arrayListItems ) === 0 ) {
			$overflowMenuClass .= ' emptyPortlet';
		}
		return $overflowMenuClass;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$portletData = $this->portletData;
		$user = $this->user;
		$userLinksCount = count( $portletData['data-user-menu']['array-items'] );
		$isDefaultAnonUserLinks = $userLinksCount <= 3;

		$preferencesData = $portletData[ 'data-user-interface-preferences' ]['array-items'] ?? [];
		$preferencesMenu = new VectorComponentMenu( [
			'id' => 'p-vector-user-menu-preferences',
			'class' => self::getOverflowMenuClass( $preferencesData ),
			'label' => null,
			'html-items' => null,
			'array-list-items' => $preferencesData,
		], [
			'button' => true,
			'collapsible' => true,
		] );

		$userPageData = $portletData[ 'data-user-page' ]['array-items'] ?? [];
		$userPageMenu = new VectorComponentMenu( [
			'id' => 'p-vector-user-menu-userpage',
			'class' => self::getOverflowMenuClass( $userPageData ),
			'label' => null,
			'html-items' => null,
			'array-list-items' => $userPageData,
			'html-after-portal' => $portletData[ 'data-user-page' ]['html-after-portal'] ?? '',
		], [
			'collapsible' => true,
			'icon' => null,
		] );

		$notificationsData = $portletData[ 'data-notifications' ]['array-items'] ?? [];
		$notificationsMenu = new VectorComponentMenu( [
			'id' => 'p-vector-user-menu-notifications',
			'class' => self::getOverflowMenuClass( $notificationsData ),
			'label' => null,
			'html-items' => null,
			'array-list-items' => $notificationsData,
		], [
			'button' => [
				'iconOnly' => true,
			],
		], [
			'pt-talk-alert' => [
				'button' => false,
				'icon' => null,
			],
		] );

		$overflowKeys = self::getOverflowKeys( $user );
		$overflowData = array_map(
			static function ( $item ) {
				// Since we're creating duplicate icons
				$item['id'] .= '-2';
				return $item;
			},
			// array_filter preserves keys so use array_values to restore array.
			array_values(
				array_filter(
					$portletData['data-user-menu']['array-items'] ?? [],
					static function ( $item ) use ( $overflowKeys ) {
						$name = $item['name'];
						return in_array( $name, $overflowKeys );
					}
				)
			)
		);

		// Logged in overflow menu items are icon only buttons
		// Styles for anon overflow menu is generally collapsible with no icon
		// the overflow donate link (pt-sitesupport-2) is an exception
		$overflowStyles = $this->user->isRegistered() ? [
			'button' => [
				'iconOnly' => true,
			],
			'collapsible' => true,
		] : [
			'button' => true,
			'collapsible' => true,
		];

		// Disable buttons and hide icons for the account actions:
		$overflowOverrides = [];
		foreach ( self::ACCOUNT_MENU_ITEM_KEYS as $key ) {
			$overflowOverrides['pt-' . $key . '-2'] = [
				'button' => false,
				'icon' => null,
				'collapsible' => true,
			];
		}
		// also disable button and hide icon for donate (T425721)
		$overflowOverrides['pt-sitesupport-2'] = [
			'button' => false,
			'icon' => null,
			'collapsible' => true,
		];

		$overflowMenu = new VectorComponentMenu( [
			'id' => 'p-vector-user-menu-overflow',
			'class' => self::getOverflowMenuClass( $overflowData ),
			'label' => null,
			'html-items' => null,
			'array-list-items' => $overflowData,
		], $overflowStyles, $overflowOverrides );

		return [
			'is-wide' => array_filter(
				[ $overflowData, $notificationsData, $userPageData, $preferencesData ]
			) !== [],
			'data-user-links-notifications' => $notificationsMenu->getTemplateData(),
			'data-user-links-overflow' => $overflowMenu->getTemplateData(),
			'data-user-links-preferences' => $preferencesMenu->getTemplateData(),
			'data-user-links-user-page' => $userPageMenu->getTemplateData(),
			'data-user-links-dropdown' => $this->getDropdown(
				$isDefaultAnonUserLinks, $userLinksCount )->getTemplateData(),
			'data-user-links-menus' => array_map( static function ( $menu ) {
				return $menu->getTemplateData();
			}, $this->getMenus() ),
		];
	}
}
