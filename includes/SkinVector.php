<?php
/**
 * Vector - Modern version of MonoBook with fresh look and many usability
 * improvements.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Skins
 */

use MediaWiki\MediaWikiServices;
use Vector\Constants;
use Vector\VectorServices;

/**
 * Skin subclass for Vector
 * @ingroup Skins
 * Skins extending SkinVector are not supported
 * @package Vector
 * @internal
 */
class SkinVector extends SkinMustache {
	/** @var int */
	private const MENU_TYPE_DEFAULT = 0;
	/** @var int */
	private const MENU_TYPE_TABS = 1;
	/** @var int */
	private const MENU_TYPE_DROPDOWN = 2;
	private const MENU_TYPE_PORTAL = 3;

	/**
	 * T243281: Code used to track clicks to opt-out link.
	 *
	 * The "vct" substring is used to describe the newest "Vector" (non-legacy)
	 * feature. The "w" describes the web platform. The "1" describes the version
	 * of the feature.
	 *
	 * @see https://wikitech.wikimedia.org/wiki/Provenance
	 * @var string
	 */
	private const OPT_OUT_LINK_TRACKING_CODE = 'vctw1';

	/**
	 * Whether or not the legacy version of the skin is being used.
	 *
	 * @return bool
	 */
	private function isLegacy() : bool {
		$isLatestSkinFeatureEnabled = MediaWikiServices::getInstance()
			->getService( Constants::SERVICE_FEATURE_MANAGER )
			->isFeatureEnabled( Constants::FEATURE_LATEST_SKIN );

		return !$isLatestSkinFeatureEnabled;
	}

	/**
	 * Overrides template, styles and scripts module when skin operates
	 * in legacy mode.
	 *
	 * @inheritDoc
	 * @param array|null $options Note; this param is only optional for internal purpose.
	 * 		Do not instantiate Vector, use SkinFactory to create the object instead.
	 * 		If you absolutely must to, this paramater is required; you have to provide the
	 * 		skinname with the `name` key. That's do it with `new SkinVector( ['name' => 'vector'] )`.
	 * 		Failure to do that, will lead to fatal exception.
	 */
	public function __construct( $options = [] ) {
		if ( $this->isLegacy() ) {
			$options['scripts'] = [ 'skins.vector.legacy.js' ];
			$options['styles'] = [ 'skins.vector.styles.legacy' ];
			$options['template'] = 'skin-legacy';
		}
		$options['templateDirectory'] = __DIR__ . '/templates';
		parent::__construct( $options );
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData() : array {
		$contentNavigation = $this->buildContentNavigationUrls();
		$skin = $this;
		$out = $skin->getOutput();
		$title = $out->getTitle();

		$featureManager = VectorServices::getFeatureManager();

		// Naming conventions for Mustache parameters.
		//
		// Value type (first segment):
		// - Prefix "is" or "has" for boolean values.
		// - Prefix "msg-" for interface message text.
		// - Prefix "html-" for raw HTML.
		// - Prefix "data-" for an array of template parameters that should be passed directly
		//   to a template partial.
		// - Prefix "array-" for lists of any values.
		//
		// Source of value (first or second segment)
		// - Segment "page-" for data relating to the current page (e.g. Title, WikiPage, or OutputPage).
		// - Segment "hook-" for any thing generated from a hook.
		//   It should be followed by the name of the hook in hyphenated lowercase.
		//
		// Conditionally used values must use null to indicate absence (not false or '').

		$parentData = parent::getTemplateData();

		$commonSkinData = array_merge( $parentData, [
			'page-isarticle' => (bool)$out->isArticle(),

			// Remember that the string '0' is a valid title.
			// From OutputPage::getPageTitle, via ::setPageTitle().
			'html-title' => $out->getPageTitle(),

			'html-categories' => $skin->getCategories(),

			'input-location' => $this->getSearchBoxInputLocation(),

			'sidebar-visible' => $this->isSidebarVisible(),
		] );

		if ( $skin->getUser()->isRegistered() ) {
			// Note: This data is also passed to legacy template where it is unused.
			$commonSkinData['data-emphasized-sidebar-action'] = [
				'href' => SpecialPage::getTitleFor(
					'Preferences',
					false,
					'mw-prefsection-rendering-skin-skin-prefs'
				)->getLinkURL( 'wprov=' . self::OPT_OUT_LINK_TRACKING_CODE ),
			];
		}

		return $commonSkinData;
	}

	/**
	 * Gets the value of the "input-location" parameter for the SearchBox Mustache template.
	 *
	 * @return string Either `Constants::SEARCH_BOX_INPUT_LOCATION_DEFAULT` or
	 *  `Constants::SEARCH_BOX_INPUT_LOCATION_MOVED`
	 */
	private function getSearchBoxInputLocation() : string {
		if ( $this->isLegacy() ) {
			return Constants::SEARCH_BOX_INPUT_LOCATION_DEFAULT;
		}

		return Constants::SEARCH_BOX_INPUT_LOCATION_MOVED;
	}

	/**
	 * Determines wheather the initial state of sidebar is visible on not
	 *
	 * @return bool
	 */
	private function isSidebarVisible() {
		$skin = $this->getSkin();
		if ( $skin->getUser()->isRegistered() ) {
			$userPrefSidebarState = $skin->getUser()->getOption(
				Constants::PREF_KEY_SIDEBAR_VISIBLE
			);

			$defaultLoggedinSidebarState = $this->getConfig()->get(
				Constants::CONFIG_KEY_DEFAULT_SIDEBAR_VISIBLE_FOR_AUTHORISED_USER
			);

			// If the sidebar user preference has been set, return that value,
			// if not, then the default sidebar state for logged-in users.
			return ( $userPrefSidebarState !== null )
				? (bool)$userPrefSidebarState
				: $defaultLoggedinSidebarState;
		}
		return $this->getConfig()->get(
			Constants::CONFIG_KEY_DEFAULT_SIDEBAR_VISIBLE_FOR_ANONYMOUS_USER
		);
	}

	/**
	 * helper for applying Vector menu classes to portlets
	 * @param array $portletData returned by SkinMustache to decorate
	 * @param int $type representing one of the menu types (see MENU_TYPE_* constants)
	 * @return array modified version of portletData input
	 */
	private function decoratePortletClass(
		array $portletData,
		int $type = self::MENU_TYPE_DEFAULT
	) {
		$extraClasses = [
			self::MENU_TYPE_DROPDOWN => 'vector-menu vector-menu-dropdown',
			self::MENU_TYPE_TABS => 'vector-menu vector-menu-tabs',
			self::MENU_TYPE_PORTAL => 'vector-menu vector-menu-portal portal',
			self::MENU_TYPE_DEFAULT => 'vector-menu',
		];
		$class = $portletData['class'];
		$portletData['class'] = trim( "$class $extraClasses[$type]" );
		return $portletData;
	}

	/**
	 * @inheritDoc
	 * @return array
	 */
	protected function getPortletData(
		$label,
		array $urls = []
	) : array {
		switch ( $label ) {
			case 'actions':
			case 'variants':
				$type = self::MENU_TYPE_DROPDOWN;
				break;
			case 'views':
			case 'namespaces':
				$type = self::MENU_TYPE_TABS;
				break;
			case 'personal':
				$type = self::MENU_TYPE_DEFAULT;
				break;
			default:
				$type = self::MENU_TYPE_PORTAL;
				break;
		}

		$portletData = $this->decoratePortletClass(
			parent::getPortletData( $label, $urls ),
			$type
		);

		// Special casing for Variant to change label to selected.
		// Hopefully we can revisit and possibly remove this code when the language switcher is moved.
		if ( $label === 'variants' ) {
			foreach ( $urls as $key => $item ) {
			// Check the class of the item for a `selected` class and if so, propagate the items
			// label to the main label.
				if ( isset( $item['class'] ) && stripos( $item['class'], 'selected' ) !== false ) {
					$portletData['label'] = $item['text'];
				}
			}
		}

		return $portletData + [
			'is-dropdown' => $type === self::MENU_TYPE_DROPDOWN,
		];
	}
}
