<?php
namespace MediaWiki\Skins\Vector\Components;

use Linker;
use MediaWiki\Skins\Vector\Constants;
use MediaWiki\Skins\Vector\VectorServices;
use Message;
use MessageLocalizer;
use Title;
use User;

/**
 * VectorComponentUserLinks component
 */
class VectorComponentUserLinks implements VectorComponent {
	/** @var MessageLocalizer */
	private $localizer;
	/** @var User */
	private $user;
	/** @var VectorComponentMenu */
	private $userMenu;
	/** @var VectorComponentMenu */
	private $overflowMenu;
	/** @var VectorComponentMenu */
	private $accountMenu;

	/**
	 * @param MessageLocalizer $localizer
	 * @param User $user
	 * @param VectorComponentMenu $userMenu menu of icon only links
	 * @param VectorComponentMenu $overflowMenu menu that appears in dropdown
	 * @param VectorComponentMenu $accountMenu links that appear inside dropdown
	 *  for login, logout or create account.
	 */
	public function __construct(
		MessageLocalizer $localizer,
		User $user,
		VectorComponentMenu $userMenu,
		VectorComponentMenu $overflowMenu,
		VectorComponentMenu $accountMenu
	) {
		$this->localizer = $localizer;
		$this->user = $user;
		$this->userMenu = $userMenu;
		$this->overflowMenu = $overflowMenu;
		$this->accountMenu = $accountMenu;
	}

	/**
	 * @param string $key
	 * @return Message
	 */
	private function msg( $key ): Message {
		return $this->localizer->msg( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$userMenu = $this->userMenu;
		$overflowMenu = $this->overflowMenu;
		$user = $this->user;
		$isAnon = !$user->isRegistered();
		$isRegisteredUser = $user->isRegistered();
		// T317789: Core can undesirably add an 'emptyPortlet' class that hides the
		// user menu. This is a result of us manually removing items from the menu
		// in Hooks::updateUserLinksDropdownItems which can make
		// SkinTemplate::getPortletData apply the `emptyPortlet` class if there are
		// no menu items. Since we subsequently add menu items in
		// SkinVector::getUserLinksTemplateData, the `emptyPortlet` class is
		// innaccurate. This is why we add the desired classes, `mw-portlet` and
		// `mw-portlet-personal` here instead. This can potentially be removed upon
		// completion of T319356.
		//
		// Also, add target class to apply different icon to personal menu dropdown for logged in users.
		$class = 'mw-portlet mw-portlet-personal vector-user-menu';
		if ( VectorServices::getFeatureManager()->isFeatureEnabled( Constants::FEATURE_PAGE_TOOLS ) ) {
			$class .= ' mw-ui-icon-flush-right';
		}
		$class .= $isRegisteredUser ?
			' vector-user-menu-logged-in' :
			' vector-user-menu-logged-out';

		$tooltip = '';
		if ( $user->isTemp() ) {
			$icon = 'userAnonymous';
		} elseif ( $isRegisteredUser ) {
			$icon = 'userAvatar';
		} else {
			$icon = 'ellipsis';
			// T287494 We use tooltip messages to provide title attributes on hover over certain menu icons.
			// For modern Vector, the "tooltip-p-personal" key is set to "User menu" which is appropriate for
			// the user icon (dropdown indicator for user links menu) for logged-in users.
			// This overrides the tooltip for the user links menu icon which is an ellipsis for anonymous users.
			$tooltip = Linker::tooltip( 'vector-anon-user-menu-title' ) ?? '';
		}
		$userMenuDropdown = new VectorComponentDropdown(
			'p-personal', $this->msg( 'personaltools' )->text(), $class, $icon, $tooltip
		);
		$additionalData = [];
		// T317789: The `anontalk` and `anoncontribs` links will not be added to
		// the menu if `$wgGroupPermissions['*']['edit']` === false which can
		// leave the menu empty due to our removal of other user menu items in
		// `Hooks::updateUserLinksDropdownItems`. In this case, we do not want
		// to render the anon "learn more" link.
		if ( $isAnon && count( $userMenu ) > 0 ) {
			$learnMoreLink = new VectorComponentIconLink(
				Title::newFromText( $this->msg( 'vector-intro-page' )->text() )->getLocalURL(),
				$this->msg( 'vector-anon-user-menu-pages-learn' )->text(),
				null,
				$this->localizer,
				'vector-anon-user-menu-pages'
			);

			$additionalData = [
				'data-anon-editor' => [
					'data-link-learn-more' => $learnMoreLink->getTemplateData(),
					'msgLearnMore' => $this->msg( 'vector-anon-user-menu-pages' )
				]
			];
		}

		return $additionalData + [
			'is-temp-user' => $user->isTemp(),
			'is-wide' => count( $overflowMenu ) > 3,
			'data-user-menu-overflow' => $overflowMenu->getTemplateData(),
			'data-user-menu-dropdown' => $userMenuDropdown->getTemplateData(),
			'data-dropdown-menu' => $userMenu->getTemplateData(),
			'data-account-links' => $this->accountMenu->getTemplateData(),
		];
	}
}
