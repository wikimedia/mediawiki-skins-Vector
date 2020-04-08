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

/**
 * QuickTemplate subclass for Vector
 * @ingroup Skins
 */
class VectorTemplate extends BaseTemplate {
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

	/** @var TemplateParser */
	private $templateParser;
	/** @var string File name of the root (master) template without folder path and extension */
	private $templateRoot;

	/** @var bool */
	private $isLegacy;

	/**
	 * @param Config $config
	 * @param TemplateParser $templateParser
	 * @param bool $isLegacy
	 */
	public function __construct(
		Config $config,
		TemplateParser $templateParser,
		bool $isLegacy
	) {
		parent::__construct( $config );

		$this->templateParser = $templateParser;
		$this->isLegacy = $isLegacy;
		$this->templateRoot = $isLegacy ? 'legacy' : 'index';
	}

	/**
	 * The template parser might be undefined. This function will check if it set first
	 *
	 * @return TemplateParser
	 */
	protected function getTemplateParser() {
		if ( $this->templateParser === null ) {
			throw new \LogicException(
				'TemplateParser has to be set first via setTemplateParser method'
			);
		}
		return $this->templateParser;
	}

	/**
	 * @return array Returns an array of data shared between Vector and legacy
	 * Vector.
	 */
	private function getSkinData() : array {
		$contentNavigation = $this->get( 'content_navigation', [] );
		$this->set( 'namespace_urls', $contentNavigation[ 'namespaces' ] );
		$this->set( 'view_urls', $contentNavigation[ 'views' ] );
		$this->set( 'action_urls', $contentNavigation[ 'actions' ] );
		$this->set( 'variant_urls', $contentNavigation[ 'variants' ] );

		// Move the watch/unwatch star outside of the collapsed "actions" menu to the main "views" menu
		if ( $this->config->get( 'VectorUseIconWatch' ) ) {
			$mode = ( $this->getSkin()->getRelevantTitle()->isWatchable() &&
						MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(
							$this->getSkin()->getUser(),
							'viewmywatchlist'
						) &&
						MediaWikiServices::getInstance()->getWatchedItemStore()->isWatched(
							$this->getSkin()->getUser(),
							$this->getSkin()->getRelevantTitle()
						)
					) ? 'unwatch' : 'watch';

			$actionUrls = $this->get( 'action_urls', [] );
			if ( array_key_exists( $mode, $actionUrls ) ) {
				$viewUrls = $this->get( 'view_urls' );
				$viewUrls[ $mode ] = $actionUrls[ $mode ];
				unset( $actionUrls[ $mode ] );
				$this->set( 'view_urls', $viewUrls );
				$this->set( 'action_urls', $actionUrls );
			}
		}

		ob_start();
		Hooks::run( 'VectorBeforeFooter', [], '1.35' );
		$htmlHookVectorBeforeFooter = ob_get_contents();
		ob_end_clean();

		// Naming conventions for Mustache parameters.
		//
		// Value type (first segment):
		// - Prefix "is" for boolean values.
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
		$commonSkinData = [
			'html-headelement' => $this->get( 'headelement', '' ),
			'html-sitenotice' => $this->get( 'sitenotice', null ),
			'html-indicators' => $this->getIndicators(),
			'page-langcode' => $this->getSkin()->getTitle()->getPageViewLanguage()->getHtmlCode(),
			'page-isarticle' => (bool)$this->get( 'isarticle' ),

			// Remember that the string '0' is a valid title.
			// From OutputPage::getPageTitle, via ::setPageTitle().
			'html-title' => $this->get( 'title', '' ),

			'html-prebodyhtml' => $this->get( 'prebodyhtml', '' ),
			'msg-tagline' => $this->getMsg( 'tagline' )->text(),
			// TODO: mediawiki/SkinTemplate should expose langCode and langDir properly.
			'html-userlangattributes' => $this->get( 'userlangattributes', '' ),
			// From OutputPage::getSubtitle()
			'html-subtitle' => $this->get( 'subtitle', '' ),

			// TODO: Use directly Skin::getUndeleteLink() directly.
			// Always returns string, cast to null if empty.
			'html-undelete' => $this->get( 'undelete', null ) ?: null,

			// From Skin::getNewtalks(). Always returns string, cast to null if empty.
			'html-newtalk' => $this->get( 'newtalk', '' ) ?: null,

			'msg-jumptonavigation' => $this->getMsg( 'vector-jumptonavigation' )->text(),
			'msg-jumptosearch' => $this->getMsg( 'vector-jumptosearch' )->text(),

			// Result of OutputPage::addHTML calls
			'html-bodycontent' => $this->get( 'bodycontent' ),

			'html-printfooter' => $this->get( 'printfooter', null ),
			'html-catlinks' => $this->get( 'catlinks', '' ),
			'html-dataAfterContent' => $this->get( 'dataAfterContent', '' ),
			// From MWDebug::getHTMLDebugLog (when $wgShowDebug is enabled)
			'html-debuglog' => $this->get( 'debughtml', '' ),
			// From BaseTemplate::getTrail (handles bottom JavaScript)
			'html-printtail' => $this->getTrail() . '</body></html>',
			'data-footer' => [
				'html-userlangattributes' => $this->get( 'userlangattributes', '' ),
				'html-hook-vector-before-footer' => $htmlHookVectorBeforeFooter,
				'array-footer-rows' => $this->getTemplateFooterRows(),
			],
			'html-navigation-heading' => $this->getMsg( 'navigation-heading' ),
			'data-personal-menu' => $this->buildPersonalProps(),
			'data-namespace-tabs' => $this->buildNamespacesProps(),
			'data-variants' => $this->buildVariantsProps(),
			'data-page-actions' => $this->buildViewsProps(),
			'data-page-actions-more' => $this->buildActionsProps(),
			'data-search-box' => $this->buildSearchProps(),
			'data-sidebar' => [
					'html-logo-attributes' => Xml::expandAttributes(
						Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) + [
							'class' => 'mw-wiki-logo',
							'href' => Skin::makeMainPageUrl(),
						]
					)
			] + $this->buildSidebarProps( $this->get( 'sidebar', [] ) ),
		];

		// The following logic is unqiue to Vector (not used by legacy Vector) and
		// is planned to be moved in a follow-up patch.
		if ( !$this->isLegacy && $this->getSkin()->getUser()->isLoggedIn() ) {
			$commonSkinData['data-sidebar']['data-emphasized-sidebar-action'] = [
				'href' => SpecialPage::getTitleFor(
					'Preferences',
					false,
					// FIXME: should be mw-prefsection-rendering-skin-skin-prefs but this doesn't currently work
					// possibly due to the issues T246491
					'mw-prefsection-rendering'
				)->getLinkURL( 'wprov=' . self::OPT_OUT_LINK_TRACKING_CODE ),
				'text' => $this->getMsg( 'vector-opt-out' )->text()
			];
		}

		return $commonSkinData;
	}

	/**
	 * Renders the entire contents of the HTML page.
	 */
	public function execute() {
		$tp = $this->getTemplateParser();
		echo $tp->processTemplate( $this->templateRoot, $this->getSkinData() );
	}

	/**
	 * Get rows that make up the footer
	 * @return array for use in Mustache template describing the footer elements.
	 */
	private function getTemplateFooterRows() : array {
		$footerRows = [];
		foreach ( $this->getFooterLinks() as $category => $links ) {
			$items = [];
			$rowId = "footer-$category";

			foreach ( $links as $link ) {
				$items[] = [
					'id' => "$rowId-$link",
					'html' => $this->get( $link, '' ),
				];
			}

			$footerRows[] = [
				'id' => $rowId,
				'className' => '',
				'array-items' => $items
			];
		}

		// If footer icons are enabled append to the end of the rows
		$footerIcons = $this->getFooterIcons( 'icononly' );
		if ( count( $footerIcons ) > 0 ) {
			$items = [];
			foreach ( $footerIcons as $blockName => $blockIcons ) {
				$html = '';
				foreach ( $blockIcons as $icon ) {
					$html .= $this->getSkin()->makeFooterIcon( $icon );
				}
				$items[] = [
					'id' => 'footer-' . htmlspecialchars( $blockName ) . 'ico',
					'html' => $html,
				];
			}

			$footerRows[] = [
				'id' => 'footer-icons',
				'className' => 'noprint',
				'array-items' => $items,
			];
		}

		return $footerRows;
	}

	/**
	 * Render a series of portals
	 *
	 * @param array $portals
	 * @return array
	 */
	private function buildSidebarProps( array $portals ) : array {
		$props = [];
		// Force the rendering of the following portals
		if ( !isset( $portals['TOOLBOX'] ) ) {
			$portals['TOOLBOX'] = true;
		}
		if ( !isset( $portals['LANGUAGES'] ) ) {
			$portals['LANGUAGES'] = true;
		}
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
					$portal = $this->buildPortalProps( 'tb', $this->getToolbox(), 'toolbox',
						'SkinTemplateToolboxEnd' );
					ob_start();
					Hooks::run( 'VectorAfterToolbox', [], '1.35' );
					$props[] = $portal + [
						'html-hook-vector-after-toolbox' => ob_get_clean(),
					];
					break;
				case 'LANGUAGES':
					if ( $this->get( 'language_urls' ) !== false ) {
						$props[] = $this->buildPortalProps(
							'lang', $this->get( 'language_urls' ), 'otherlanguages'
						);
					}
					break;
				default:
					$props[] = $this->buildPortalProps( $name, $content );
					break;
			}
		}

		return [
			'array-portals-rest' => array_slice( $props, 1 ),
			'array-portals-first' => $props[0] ?? null,
		];
	}

	/**
	 * @param string $name
	 * @param array|string $content
	 * @param null|string $msg
	 * @param null|string|array $hook
	 * @return array
	 */
	private function buildPortalProps( $name, $content, $msg = null, $hook = null ) : array {
		if ( $msg === null ) {
			$msg = $name;
		}

		$msgObj = $this->getMsg( $msg );

		$props = [
			'portal-id' => "p-$name",
			'html-tooltip' => Linker::tooltip( 'p-' . $name ),
			'msg-label' => $msgObj->exists() ? $msgObj->text() : $msg,
			'msg-label-id' => "p-$name-label",
			'html-userlangattributes' => $this->get( 'userlangattributes', '' ),
			'html-portal-content' => '',
			'html-after-portal' => $this->getAfterPortlet( $name ),
		];

		if ( is_array( $content ) ) {
			$props['html-portal-content'] .= '<ul>';
			foreach ( $content as $key => $val ) {
				$props['html-portal-content'] .= $this->makeListItem( $key, $val );
			}
			if ( $hook !== null ) {
				// Avoid PHP 7.1 warning
				$skin = $this;
				ob_start();
				Hooks::run( $hook, [ &$skin, true ] );
				$props['html-portal-content'] .= ob_get_contents();
				ob_end_clean();
			}
			$props['html-portal-content'] .= '</ul>';
		} else {
			// Allow raw HTML block to be defined by extensions
			$props['html-portal-content'] = $content;
		}

		return $props;
	}

	/**
	 * @inheritDoc
	 */
	public function makeListItem( $key, $item, $options = [] ) {
		// For fancy styling of watch/unwatch star
		if (
			$this->config->get( 'VectorUseIconWatch' )
			&& ( $key === 'watch' || $key === 'unwatch' )
		) {
			if ( !isset( $item['class'] ) ) {
				$item['class'] = '';
			}
			$item['class'] = rtrim( 'icon ' . $item['class'], ' ' );
			$item['primary'] = true;
		}

		// Add CSS class 'collapsible' to links which are not marked as "primary"
		if (
			isset( $options['vector-collapsible'] ) && $options['vector-collapsible'] ) {
			if ( !isset( $item['class'] ) ) {
				$item['class'] = '';
			}
			$item['class'] = rtrim( 'collapsible ' . $item['class'], ' ' );
		}

		return parent::makeListItem( $key, $item, $options );
	}

	/**
	 * @return array
	 */
	private function buildNamespacesProps() : array {
		$props = [
			'tabs-id' => 'p-namespaces',
			'empty-portlet' => ( count( $this->get( 'namespace_urls', [] ) ) == 0 ) ? 'emptyPortlet' : '',
			'label-id' => 'p-namespaces-label',
			'msg-label' => $this->getMsg( 'namespaces' )->text(),
			'html-userlangattributes' => $this->get( 'userlangattributes', '' ),
			'html-items' => '',
		];

		foreach ( $this->get( 'namespace_urls', [] ) as $key => $item ) {
			$props[ 'html-items' ] .= $this->makeListItem( $key, $item );
		}

		return $props;
	}

	/**
	 * @return array
	 */
	private function buildVariantsProps() : array {
		$props = [
			'empty-portlet' => ( count( $this->get( 'variant_urls', [] ) ) == 0 ) ? 'emptyPortlet' : '',
			'menu-id' => 'p-variants',
			'menu-label-id' => 'p-variants-label',
			'msg-label' => $this->getMsg( 'variants' )->text(),
			'html-items' => '',
		];

		// Replace the label with the name of currently chosen variant, if any
		$variantUrls = $this->get( 'variant_urls', [] );
		foreach ( $variantUrls as $item ) {
			if ( isset( $item['class'] ) && stripos( $item['class'], 'selected' ) !== false ) {
				$props['msg-label'] = $item['text'];
				break;
			}
		}

		foreach ( $variantUrls as $key => $item ) {
			$props['html-items'] .= $this->makeListItem( $key, $item );
		}

		return $props;
	}

	/**
	 * @return array
	 */
	private function buildViewsProps() : array {
		$props = [
			'tabs-id' => 'p-views',
			'empty-portlet' => ( count( $this->get( 'view_urls', [] ) ) == 0 ) ? 'emptyPortlet' : '',
			'label-id' => 'p-views-label',
			'msg-label' => $this->getMsg( 'views' )->text(),
			'html-userlangattributes' => $this->get( 'userlangattributes', '' ),
			'html-items' => '',
		];
		$viewUrls = $this->get( 'view_urls', [] );
		foreach ( $viewUrls as $key => $item ) {
			$props[ 'html-items' ] .= $this->makeListItem( $key, $item, [
				'vector-collapsible' => true,
			] );
		}

		return $props;
	}

	/**
	 * @return array
	 */
	private function buildActionsProps() : array {
		$props = [
			'empty-portlet' => ( count( $this->get( 'action_urls', [] ) ) == 0 ) ? 'emptyPortlet' : '',
			'msg-label' => $this->getMsg( 'vector-more-actions' )->text(),
			'menu-id' => 'p-cactions',
			'menu-label-id' => 'p-cactions-label',
			'html-userlangattributes' => $this->get( 'userlangattributes', '' ),
			'html-items' => '',
		];

		$actionUrls = $this->get( 'action_urls', [] );
		foreach ( $actionUrls as $key => $item ) {
			$props['html-items'] .= $this->makeListItem( $key, $item );
		}

		return $props;
	}

	/**
	 * @return array
	 */
	private function buildPersonalProps() : array {
		$personalTools = $this->getPersonalTools();
		$props = [
			'empty-portlet' => ( count( $this->get( 'personal_urls', [] ) ) == 0 ) ? 'emptyPortlet' : '',
			'msg-label' => $this->getMsg( 'personaltools' )->text(),
			'html-userlangattributes' => $this->get( 'userlangattributes', '' ),
			'html-loggedin' => '',
			'html-personal-tools' => '',
			'html-lang-selector' => '',

		];

		if ( !$this->getSkin()->getUser()->isLoggedIn() && User::groupHasPermission( '*', 'edit' ) ) {
			$props['html-loggedin'] =
				Html::element( 'li',
					[ 'id' => 'pt-anonuserpage' ],
					$this->getMsg( 'notloggedin' )->text()
				);
		}

		if ( array_key_exists( 'uls', $personalTools ) ) {
			$props['html-lang-selector'] = $this->makeListItem( 'uls', $personalTools[ 'uls' ] );
			unset( $personalTools[ 'uls' ] );
		}

		foreach ( $personalTools as $key => $item ) {
			$props['html-personal-tools'] .= $this->makeListItem( $key, $item );
		}

		return $props;
	}

	/**
	 * @return array
	 */
	private function buildSearchProps() : array {
		$props = [
			'searchHeaderAttrsHTML' => $this->get( 'userlangattributes', '' ),
			'searchActionURL' => $this->get( 'wgScript', '' ),
			'searchDivID' => $this->config->get( 'VectorUseSimpleSearch' ) ? 'simpleSearch' : '',
			'searchInputHTML' => $this->makeSearchInput( [ 'id' => 'searchInput' ] ),
			'titleHTML' => Html::hidden( 'title', $this->get( 'searchtitle', null ) ),
			'fallbackSearchButtonHTML' => $this->makeSearchButton(
				'fulltext',
				[ 'id' => 'mw-searchButton', 'class' => 'searchButton mw-fallbackSearchButton' ]
			),
			'searchButtonHTML' => $this->makeSearchButton(
				'go',
				[ 'id' => 'searchButton', 'class' => 'searchButton' ]
			),
			'searchInputLabel' => $this->getMsg( 'search' )
		];
		return $props;
	}
}
