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

/**
 * Skin subclass for Vector
 * @ingroup Skins
 */
class SkinTemplateVector extends SkinMustache {

	/** @var array of alternate message keys for menu labels */
	private const MENU_LABEL_KEYS = [
		'cactions' => 'vector-more-actions',
		'tb' => 'toolbox',
		'personal' => 'personaltools',
		'lang' => 'otherlanguages',
	];
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

	/** @var bool */
	private $isLegacy;

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
	 */
	public function __construct( $options = [] ) {
		// Legacy overrides
		$this->isLegacy = $this->isLegacy();
		if ( $this->isLegacy ) {
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
		$mainPageHref = Skin::makeMainPageUrl();
		// From Skin::getNewtalks(). Always returns string, cast to null if empty.
		$newTalksHtml = $skin->getNewtalks() ?: null;

		$commonSkinData = parent::getTemplateData() + [
			'page-langcode' => $title->getPageViewLanguage()->getHtmlCode(),
			'page-isarticle' => (bool)$out->isArticle(),

			// Remember that the string '0' is a valid title.
			// From OutputPage::getPageTitle, via ::setPageTitle().
			'html-title' => $out->getPageTitle(),
			'msg-tagline' => $skin->msg( 'tagline' )->text(),

			'html-newtalk' => $newTalksHtml ? '<div class="usermessage">' . $newTalksHtml . '</div>' : '',

			'msg-vector-jumptonavigation' => $skin->msg( 'vector-jumptonavigation' )->text(),
			'msg-vector-jumptosearch' => $skin->msg( 'vector-jumptosearch' )->text(),

			'html-printfooter' => $skin->printSource(),
			'html-categories' => $skin->getCategories(),
			'data-footer' => $this->getFooterData(),
			'html-navigation-heading' => $skin->msg( 'navigation-heading' ),

			// Header
			'data-logos' => ResourceLoaderSkinModule::getAvailableLogos( $this->getConfig() ),
			'msg-sitetitle' => $skin->msg( 'sitetitle' )->text(),
			'msg-sitesubtitle' => $skin->msg( 'sitesubtitle' )->text(),
			'main-page-href' => $mainPageHref,

			'data-sidebar' => $this->buildSidebar(),
			'sidebar-visible' => $this->isSidebarVisible(),
			'msg-vector-action-toggle-sidebar' => $skin->msg( 'vector-action-toggle-sidebar' )->text(),
		] + $this->getMenuProps();

		// The following logic is unqiue to Vector (not used by legacy Vector) and
		// is planned to be moved in a follow-up patch.
		if ( !$this->isLegacy && $skin->getUser()->isLoggedIn() ) {
			$commonSkinData['data-sidebar']['data-emphasized-sidebar-action'] = [
				'href' => SpecialPage::getTitleFor(
					'Preferences',
					false,
					'mw-prefsection-rendering-skin-skin-prefs'
				)->getLinkURL( 'wprov=' . self::OPT_OUT_LINK_TRACKING_CODE ),
				'text' => $skin->msg( 'vector-opt-out' )->text(),
				'title' => $skin->msg( 'vector-opt-out-tooltip' )->text(),
			];
		}

		return $commonSkinData;
	}

	/**
	 * Get rows that make up the footer
	 * @return array for use in Mustache template describing the footer elements.
	 */
	private function getFooterData() : array {
		$skin = $this;
		$footerRows = [];
		foreach ( $this->getFooterLinks() as $category => $links ) {
			$items = [];
			$rowId = "footer-$category";

			foreach ( $links as $key => $link ) {
				// Link may be null. If so don't include it.
				if ( $link ) {
					$items[] = [
						'id' => "$rowId-$key",
						'html' => $link,
					];
				}
			}

			$footerRows[] = [
				'id' => $rowId,
				'className' => null,
				'array-items' => $items
			];
		}

		// If footer icons are enabled append to the end of the rows
		$footerIcons = $this->getFooterIcons();

		if ( count( $footerIcons ) > 0 ) {
			$items = [];
			foreach ( $footerIcons as $blockName => $blockIcons ) {
				$html = '';
				foreach ( $blockIcons as $icon ) {
					// Only output icons which have an image.
					// For historic reasons this mimics the `icononly` option
					// for BaseTemplate::getFooterIcons.
					if ( is_string( $icon ) || isset( $icon['src'] ) ) {
						$html .= $skin->makeFooterIcon( $icon );
					}
				}
				// For historic reasons this mimics the `icononly` option
				// for BaseTemplate::getFooterIcons. Empty rows should not be output.
				if ( $html ) {
					$items[] = [
						'id' => 'footer-' . htmlspecialchars( $blockName ) . 'ico',
						'html' => $html,
					];
				}
			}

			// Empty rows should not be output.
			// This is how Vector has behaved historically but we can revisit.
			if ( count( $items ) > 0 ) {
				$footerRows[] = [
					'id' => 'footer-icons',
					'className' => 'noprint',
					'array-items' => $items,
				];
			}
		}

		ob_start();
		Hooks::run( 'VectorBeforeFooter', [], '1.35' );
		$htmlHookVectorBeforeFooter = ob_get_contents();
		ob_end_clean();

		$data = [
			'html-hook-vector-before-footer' => $htmlHookVectorBeforeFooter,
			'array-footer-rows' => $footerRows,
		];

		return $data;
	}

	/**
	 * Determines wheather the initial state of sidebar is visible on not
	 *
	 * @return bool
	 */
	private function isSidebarVisible() {
		$skin = $this->getSkin();
		if ( $skin->getUser()->isLoggedIn() ) {
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
	 * Render a series of portals
	 *
	 * @return array
	 */
	public function buildSidebar() {
		$skin = $this;
		$portals = parent::buildSidebar();
		$props = [];
		$languages = null;

		// Render portals
		foreach ( $portals as $name => $content ) {
			if ( $content === false ) {
				continue;
			}

			// Numeric strings gets an integer when set as key, cast back - T73639
			$name = (string)$name;

			switch ( $name ) {
				case 'SEARCH':
					break;
				case 'TOOLBOX':
					$props[] = $this->getMenuData(
						'tb', $content,  self::MENU_TYPE_PORTAL
					);
					break;
				case 'LANGUAGES':
					$portal = $this->getMenuData(
						'lang', $content, self::MENU_TYPE_PORTAL
					);
					// The language portal will be added provided either
					// languages exist or there is a value in html-after-portal
					// for example to show the add language wikidata link (T252800)
					if ( count( $content ) || $portal['html-after-portal'] ) {
						$languages = $portal;
					}
					break;
				default:
					// Historically some portals have been defined using HTML rather than arrays.
					// Let's move away from that to a uniform definition.
					if ( !is_array( $content ) ) {
						$html = $content;
						$content = [];
						wfDeprecated(
							"`content` field in portal $name must be array."
								. "Previously it could be a string but this is no longer supported.",
							'1.35.0'
						);
					} else {
						$html = false;
					}
					$portal = $this->getMenuData(
						$name, $content, self::MENU_TYPE_PORTAL
					);
					if ( $html ) {
						$portal['html-items'] .= $html;
					}
					$props[] = $portal;
					break;
			}
		}

		$firstPortal = $props[0] ?? null;
		if ( $firstPortal ) {
			$firstPortal[ 'class' ] .= ' portal-first';
		}

		return [
			'has-logo' => $this->isLegacy,
			'html-logo-attributes' => Xml::expandAttributes(
				Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) + [
					'class' => 'mw-wiki-logo',
					'href' => Skin::makeMainPageUrl(),
				]
			),
			'array-portals-rest' => array_slice( $props, 1 ),
			'data-portals-first' => $firstPortal,
			'data-portals-languages' => $languages,
		];
	}

	/**
	 * @param string $label to be used to derive the id and human readable label of the menu
	 *  If the key has an entry in the constant MENU_LABEL_KEYS then that message will be used for the
	 *  human readable text instead.
	 * @param array $urls to convert to list items stored as string in html-items key
	 * @param int $type of menu (optional) - a plain list (MENU_TYPE_DEFAULT),
	 *   a tab (MENU_TYPE_TABS) or a dropdown (MENU_TYPE_DROPDOWN)
	 * @param array $options (optional) to be passed to makeListItem
	 * @param bool $setLabelToSelected (optional) the menu label will take the value of the
	 *  selected item if found.
	 * @return array
	 */
	private function getMenuData(
		string $label,
		array $urls = [],
		int $type = self::MENU_TYPE_DEFAULT,
		array $options = [],
		bool $setLabelToSelected = false
	) : array {
		$skin = $this->getSkin();
		$extraClasses = [
			self::MENU_TYPE_DROPDOWN => 'vector-menu vector-menu-dropdown vectorMenu',
			self::MENU_TYPE_TABS => 'vector-menu vector-menu-tabs vectorTabs',
			self::MENU_TYPE_PORTAL => 'vector-menu vector-menu-portal portal',
			self::MENU_TYPE_DEFAULT => 'vector-menu',
		];
		// A list of classes to apply the list element and override the default behavior.
		$listClasses = [
			// `.menu` is on the portal for historic reasons.
			// It should not be applied elsewhere per T253329.
			self::MENU_TYPE_DROPDOWN => 'menu vector-menu-content-list',
		];
		$isPortal = $type === self::MENU_TYPE_PORTAL;

		// For some menu items, there is no language key corresponding with its menu key.
		// These inconsitencies are captured in MENU_LABEL_KEYS
		$msgObj = $skin->msg( self::MENU_LABEL_KEYS[ $label ] ?? $label );
		$props = [
			'id' => "p-$label",
			'label-id' => "p-{$label}-label",
			// If no message exists fallback to plain text (T252727)
			'label' => $msgObj->exists() ? $msgObj->text() : $label,
			'list-classes' => $listClasses[$type] ?? 'vector-menu-content-list',
			'html-items' => '',
			'is-dropdown' => $type === self::MENU_TYPE_DROPDOWN,
			'html-tooltip' => Linker::tooltip( 'p-' . $label ),
		];

		foreach ( $urls as $key => $item ) {
			// Add CSS class 'collapsible' to all links EXCEPT watchstar.
			if (
				$key !== 'watch' && $key !== 'unwatch' &&
				isset( $options['vector-collapsible'] ) && $options['vector-collapsible'] ) {
				if ( !isset( $item['class'] ) ) {
					$item['class'] = '';
				}
				$item['class'] = rtrim( 'collapsible ' . $item['class'], ' ' );
			}
			$props['html-items'] .= $this->getSkin()->makeListItem( $key, $item, $options );

			// Check the class of the item for a `selected` class and if so, propagate the items
			// label to the main label.
			if ( $setLabelToSelected ) {
				if ( isset( $item['class'] ) && stripos( $item['class'], 'selected' ) !== false ) {
					$props['label'] = $item['text'];
				}
			}
		}

		$afterPortal = '';
		if ( $isPortal ) {
			// The BaseTemplate::getAfterPortlet method ran the SkinAfterPortlet
			// hook and if content is added appends it to the html-after-portal method.
			// This replicates that historic behaviour.
			// This code should eventually be upstreamed to SkinMustache in core.
			// Currently in production this supports the Wikibase 'edit' link.
			$content = $this->getAfterPortlet( $label );
			if ( $content !== '' ) {
				$afterPortal = Html::rawElement(
					'div',
					[ 'class' => [ 'after-portlet', 'after-portlet-' . $label ] ],
					$content
				);
			}
		}
		$props['html-after-portal'] = $afterPortal;

		// Mark the portal as empty if it has no content
		$class = ( count( $urls ) == 0 && !$props['html-after-portal'] )
			? 'vector-menu-empty emptyPortlet' : '';
		$props['class'] = trim( "$class $extraClasses[$type]" );
		return $props;
	}

	/**
	 * @return array
	 */
	private function getMenuProps() : array {
		$contentNavigation = $this->buildContentNavigationUrls();
		$personalTools = self::getPersonalToolsForMakeListItem(
			$this->buildPersonalUrls()
		);
		$skin = $this;

		// For logged out users Vector shows a "Not logged in message"
		// This should be upstreamed to core, with instructions for how to hide it for skins
		// that do not want it.
		// For now we create a dedicated list item to avoid having to sync the API internals
		// of makeListItem.
		if ( !$skin->getUser()->isLoggedIn() && User::groupHasPermission( '*', 'edit' ) ) {
			$loggedIn =
				Html::element( 'li',
					[ 'id' => 'pt-anonuserpage' ],
					$skin->msg( 'notloggedin' )->text()
				);
		} else {
			$loggedIn = '';
		}

		// This code doesn't belong here, it belongs in the UniversalLanguageSelector
		// It is here to workaround the fact that it wants to be the first item in the personal menus.
		if ( array_key_exists( 'uls', $personalTools ) ) {
			$uls = $skin->makeListItem( 'uls', $personalTools[ 'uls' ] );
			unset( $personalTools[ 'uls' ] );
		} else {
			$uls = '';
		}

		$ptools = $this->getMenuData( 'personal', $personalTools );
		// Append additional link items if present.
		$ptools['html-items'] = $uls . $loggedIn . $ptools['html-items'];

		return [
			'data-personal-menu' => $ptools,
			'data-namespace-tabs' => $this->getMenuData(
				'namespaces',
				$contentNavigation[ 'namespaces' ] ?? [],
				self::MENU_TYPE_TABS
			),
			'data-variants' => $this->getMenuData(
				'variants',
				$contentNavigation[ 'variants' ] ?? [],
				self::MENU_TYPE_DROPDOWN,
				[], true
			),
			'data-page-actions' => $this->getMenuData(
				'views',
				$contentNavigation[ 'views' ] ?? [],
				self::MENU_TYPE_TABS, [
					'vector-collapsible' => true,
				]
			),
			'data-page-actions-more' => $this->getMenuData(
				'cactions',
				$contentNavigation[ 'actions' ] ?? [],
				self::MENU_TYPE_DROPDOWN
			),
		];
	}
}
