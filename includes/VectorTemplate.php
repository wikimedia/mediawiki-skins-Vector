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

	/** @var TemplateParser */
	private $templateParser;

	/**
	 * @param TemplateParser $parser
	 */
	public function setTemplateParser( TemplateParser $parser ) {
		$this->templateParser = $parser;
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
	 * Outputs the entire contents of the HTML page
	 */
	public function execute() {
		$this->data['namespace_urls'] = $this->data['content_navigation']['namespaces'];
		$this->data['view_urls'] = $this->data['content_navigation']['views'];
		$this->data['action_urls'] = $this->data['content_navigation']['actions'];
		$this->data['variant_urls'] = $this->data['content_navigation']['variants'];

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

			if ( isset( $this->data['action_urls'][$mode] ) ) {
				$this->data['view_urls'][$mode] = $this->data['action_urls'][$mode];
				unset( $this->data['action_urls'][$mode] );
			}
		}

		ob_start();
		Hooks::run( 'VectorBeforeFooter', [], '1.35' );
		$htmlHookVectorBeforeFooter = ob_get_contents();
		ob_end_clean();

		// Naming conventions for Mustache parameters:
		// - Prefix "is" for boolean values.
		// - Prefix "msg-" for interface messages.
		// - Prefix "page-" for data relating to the current page (e.g. Title, WikiPage, or OutputPage).
		// - Prefix "hook-" for any thing generated from a hook.
		//    It should be followed by the name of the hook in hyphenated lowercase.
		// - Prefix "html-" for raw HTML (in front of other keys excluding `array-`, if applicable).
		// - Prefix "array-" for anything that is iterable (in front of other keys is applicable)
		// - Conditional values are null if absent.
		$params = [
			'html-headelement' => $this->get( 'headelement', '' ),
			'html-sitenotice' => $this->get( 'sitenotice', null ),
			'html-indicators' => $this->getIndicators(),
			'page-langcode' => $this->getSkin()->getTitle()->getPageViewLanguage()->getHtmlCode(),
			'page-isarticle' => (bool)$this->data['isarticle'],

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
			'html-footer' => $this->getTemplateParser()->processTemplate( 'Footer', [
				'html-userlangattributes' => $this->get( 'userlangattributes', '' ),
				'html-hook-vector-before-footer' => $htmlHookVectorBeforeFooter,
				'array-footer-rows' => $this->getTemplateFooterRows(),
			] ),
			'html-navigation' => $this->getTemplateParser()->processTemplate( 'Navigation', [
				'html-navigation-heading' => $this->getMsg( 'navigation-heading' ),
				'html-personal-menu' => $this->renderNavigation( [ 'PERSONAL' ] ),
				'html-navigation-left-tabs' => $this->renderNavigation( [ 'NAMESPACES', 'VARIANTS' ] ),
				'html-navigation-right-tabs' => $this->renderNavigation( [ 'VIEWS', 'ACTIONS', 'SEARCH' ] ),
				'html-logo-attributes' => Xml::expandAttributes(
					Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) + [
						'class' => 'mw-wiki-logo',
						'href' => Skin::makeMainPageUrl(),
					]
				),
				'html-portals' => $this->renderPortals( $this->data['sidebar'] ),
			] ),
		];

		// Prepare and output the HTML response
		echo $this->getTemplateParser()->processTemplate( 'index', $params );
	}

	/**
	 * Get rows that make up the footer
	 * @return array for use in Mustache template describing the footer elements.
	 */
	private function getTemplateFooterRows() {
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
	 * @return string
	 */
	protected function renderPortals( array $portals ) {
		$html = '';
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
					$html .= $this->renderPortal( 'tb', $this->getToolbox(), 'toolbox', 'SkinTemplateToolboxEnd' );
					ob_start();
					Hooks::run( 'VectorAfterToolbox', [], '1.35' );
					$html .= ob_get_clean();
					break;
				case 'LANGUAGES':
					if ( $this->data['language_urls'] !== false ) {
						$html .= $this->renderPortal(
							'lang', $this->data['language_urls'], 'otherlanguages'
						);
					}
					break;
				default:
					$html .= $this->renderPortal( $name, $content );
					break;
			}
		}
		return $html;
	}

	/**
	 * @param string $name
	 * @param array|string $content
	 * @param null|string $msg
	 * @param null|string|array $hook
	 * @return string
	 */
	protected function renderPortal( $name, $content, $msg = null, $hook = null ) {
		if ( $msg === null ) {
			$msg = $name;
		}

		$msgObj = $this->getMsg( $msg );

		$props = [
			'portal-id' => "p-$name",
			'html-tooltip' => Linker::tooltip( 'p-' . $name ),
			'msg-label' => $msgObj->exists() ? $msgObj->text() : $msg,
			'msg-label-id' => "p-$name-label",
			'html-userlangattributes' => $this->data['userlangattributes'] ?? '',
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

		return $this->getTemplateParser()->processTemplate( 'Portal', $props );
	}

	/**
	 * Render one or more navigations elements by name, automatically reversed by css
	 * when UI is in RTL mode
	 *
	 * @param array $elements
	 * @return string
	 */
	protected function renderNavigation( array $elements ) {
		$html = '';
		// Render elements
		foreach ( $elements as $name => $element ) {
			switch ( $element ) {
				case 'NAMESPACES':
					$html .= $this->renderNamespacesComponent();
					break;
				case 'VARIANTS':
					$html .= $this->renderVariantsComponent();
					break;
				case 'VIEWS':
					$html .= $this->renderViewsComponent();
					break;
				case 'ACTIONS':
					$html .= $this->renderActionsComponent();
					break;
				case 'PERSONAL':
					$html .= $this->renderPersonalComponent();
					break;
				case 'SEARCH':
					$html .= $this->renderSearchComponent();
					break;
			}
		}
		return $html;
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
	 * @return string
	 */
	private function renderNamespacesComponent() {
		$props = [
			'tabs-id' => 'p-namespaces',
			'empty-portlet' => ( count( $this->data['namespace_urls'] ) == 0 ) ? 'emptyPortlet' : '',
			'label-id' => 'p-namespaces-label',
			'msg-label' => $this->getMsg( 'namespaces' )->text(),
			'html-userlangattributes' => $this->data['userlangattributes'] ?? '',
			'html-items' => '',
		];

		foreach ( $this->data['namespace_urls'] as $key => $item ) {
			$props[ 'html-items' ] .= $this->makeListItem( $key, $item );
		}

		return $this->getTemplateParser()->processTemplate( 'VectorTabs', $props );
	}

	/**
	 * @return string
	 */
	private function renderVariantsComponent() {
		$props = [
			'empty-portlet' => ( count( $this->data['variant_urls'] ) == 0 ) ? 'emptyPortlet' : '',
			'menu-id' => 'p-variants',
			'menu-label-id' => 'p-variants-label',
			'msg-label' => $this->getMsg( 'variants' )->text(),
			'html-items' => '',
		];

		// Replace the label with the name of currently chosen variant, if any
		foreach ( $this->data['variant_urls'] as $item ) {
			if ( isset( $item['class'] ) && stripos( $item['class'], 'selected' ) !== false ) {
				$props['msg-label'] = $item['text'];
				break;
			}
		}

		foreach ( $this->data['variant_urls'] as $key => $item ) {
			$props['html-items'] .= $this->makeListItem( $key, $item );
		}

		return $this->getTemplateParser()->processTemplate( 'VectorMenu', $props );
	}

	/**
	 * @return string
	 */
	private function renderViewsComponent() {
		$props = [
			'tabs-id' => 'p-views',
			'empty-portlet' => ( count( $this->data['view_urls'] ) == 0 ) ? 'emptyPortlet' : '',
			'label-id' => 'p-views-label',
			'msg-label' => $this->getMsg( 'views' )->text(),
			'html-userlangattributes' => $this->data['userlangattributes'] ?? '',
			'html-items' => '',
		];

		foreach ( $this->data['view_urls'] as $key => $item ) {
			$props[ 'html-items' ] .= $this->makeListItem( $key, $item, [
				'vector-collapsible' => true,
			] );
		}

		return $this->getTemplateParser()->processTemplate( 'VectorTabs', $props );
	}

	/**
	 * @return string
	 */
	private function renderActionsComponent() {
		$props = [
			'empty-portlet' => ( count( $this->data['action_urls'] ) == 0 ) ? 'emptyPortlet' : '',
			'msg-label' => $this->getMsg( 'vector-more-actions' )->text(),
			'menu-id' => 'p-cactions',
			'menu-label-id' => 'p-cactions-label',
			'html-userlangattributes' => $this->data[ 'userlangattributes' ] ?? '',
			'html-items' => '',
		];

		foreach ( $this->data['action_urls'] as $key => $item ) {
			$props['html-items'] .= $this->makeListItem( $key, $item );
		}

		return $this->getTemplateParser()->processTemplate( 'VectorMenu', $props );
	}

	/**
	 * @return string
	 */
	private function renderPersonalComponent() {
		$personalTools = $this->getPersonalTools();
		$props = [
			'empty-portlet' => ( count( $this->data['personal_urls'] ) == 0 ) ? 'emptyPortlet' : '',
			'msg-label' => $this->getMsg( 'personaltools' )->text(),
			'html-userlangattributes' => $this->data[ 'userlangattributes' ] ?? '',
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

		return $this->getTemplateParser()->processTemplate( 'PersonalMenu', $props );
	}

	private function renderSearchComponent() {
		$props = [
			'searchHeaderAttrsHTML' => $this->data[ 'userlangattributes' ] ?? '',
			'searchActionURL' => $this->data[ 'wgScript' ] ?? '',
			'searchDivID' => $this->config->get( 'VectorUseSimpleSearch' ) ? 'simpleSearch' : '',
			'searchInputHTML' => $this->makeSearchInput( [ 'id' => 'searchInput' ] ),
			'titleHTML' => Html::hidden( 'title', $this->data[ 'searchtitle' ] ?? null ),
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
		return $this->getTemplateParser()->processTemplate( 'SearchBox', $props );
	}
}
