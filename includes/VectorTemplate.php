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

/**
 * QuickTemplate subclass for Vector
 * @ingroup Skins
 */
class VectorTemplate extends BaseTemplate {

	/**
	 * Outputs the entire contents of the HTML page
	 */
	public function execute() {
		$templateParser = new TemplateParser( __DIR__ . '/templates' );

		$this->data['namespace_urls'] = $this->data['content_navigation']['namespaces'];
		$this->data['view_urls'] = $this->data['content_navigation']['views'];
		$this->data['action_urls'] = $this->data['content_navigation']['actions'];
		$this->data['variant_urls'] = $this->data['content_navigation']['variants'];

		// Move the watch/unwatch star outside of the collapsed "actions" menu to the main "views" menu
		if ( $this->config->get( 'VectorUseIconWatch' ) ) {
			$mode = $this->getSkin()->getUser()->isWatched( $this->getSkin()->getRelevantTitle() )
				? 'unwatch'
				: 'watch';

			if ( isset( $this->data['action_urls'][$mode] ) ) {
				$this->data['view_urls'][$mode] = $this->data['action_urls'][$mode];
				unset( $this->data['action_urls'][$mode] );
			}
		}

		// Naming conventions for Mustache parameters:
		// - Prefix "is" for boolean values.
		// - Prefix "msg-" for interface messages.
		// - Prefix "page-" for data relating to the current page (e.g. Title, WikiPage, or OutputPage).
		// - Prefix "html-" for raw HTML (in front of other keys, if applicable).
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
			'html-printtail' => $this->getTrail(),
		];

		// TODO: Convert the rest to Mustache
		ob_start();
		?>
		<div id="mw-navigation">
			<h2><?php $this->msg( 'navigation-heading' ) ?></h2>
			<div id="mw-head">
				<?php $this->renderNavigation( $templateParser, [ 'PERSONAL' ] ); ?>
				<div id="left-navigation">
					<?php $this->renderNavigation( $templateParser, [ 'NAMESPACES', 'VARIANTS' ] ); ?>
				</div>
				<div id="right-navigation">
					<?php $this->renderNavigation( $templateParser, [ 'VIEWS', 'ACTIONS', 'SEARCH' ] ); ?>
				</div>
			</div>
			<div id="mw-panel">
				<div id="p-logo" role="banner"><a class="mw-wiki-logo" href="<?php
					echo htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] )
					?>"<?php
					echo Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) )
					?>></a></div>
				<?php $this->renderPortals( $this->data['sidebar'] ); ?>
			</div>
		</div>
		<?php Hooks::run( 'VectorBeforeFooter' ); ?>
		<div id="footer" role="contentinfo"<?php $this->html( 'userlangattributes' ) ?>>
			<?php
			foreach ( $this->getFooterLinks() as $category => $links ) {
			?>
			<ul id="footer-<?php echo $category ?>">
				<?php
				foreach ( $links as $link ) {
				?>
				<li id="footer-<?php echo $category ?>-<?php echo $link ?>"><?php $this->html( $link ) ?></li>
				<?php
				}
				?>
			</ul>
			<?php
			}
			?>
			<?php $footericons = $this->getFooterIcons( 'icononly' );
			if ( count( $footericons ) > 0 ) {
				?>
				<ul id="footer-icons" class="noprint">
					<?php
					foreach ( $footericons as $blockName => $footerIcons ) {
					?>
					<li id="footer-<?php echo htmlspecialchars( $blockName ); ?>ico">
						<?php
						foreach ( $footerIcons as $icon ) {
							echo $this->getSkin()->makeFooterIcon( $icon );
						}
						?>
					</li>
					<?php
					}
					?>
				</ul>
			<?php
			}
			?>
			<div style="clear: both;"></div>
		</div>
		<?php
		$params['html-unported'] = ob_get_contents();
		ob_end_clean();

		// Prepare and output the HTML response
		echo $templateParser->processTemplate( 'index', $params );
	}

	/**
	 * Render a series of portals
	 *
	 * @param array $portals
	 */
	protected function renderPortals( array $portals ) {
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
					$this->renderPortal( 'tb', $this->getToolbox(), 'toolbox', 'SkinTemplateToolboxEnd' );
					Hooks::run( 'VectorAfterToolbox' );
					break;
				case 'LANGUAGES':
					if ( $this->data['language_urls'] !== false ) {
						$this->renderPortal( 'lang', $this->data['language_urls'], 'otherlanguages' );
					}
					break;
				default:
					$this->renderPortal( $name, $content );
					break;
			}
		}
	}

	/**
	 * @param string $name
	 * @param array|string $content
	 * @param null|string $msg
	 * @param null|string|array $hook
	 */
	protected function renderPortal( $name, $content, $msg = null, $hook = null ) {
		if ( $msg === null ) {
			$msg = $name;
		}
		$msgObj = $this->getMsg( $msg );
		$labelId = Sanitizer::escapeIdForAttribute( "p-$name-label" );
		?>
		<div class="portal" role="navigation" id="<?php
		echo htmlspecialchars( Sanitizer::escapeIdForAttribute( "p-$name" ) )
		?>"<?php
		echo Linker::tooltip( 'p-' . $name )
		?> aria-labelledby="<?php echo htmlspecialchars( $labelId ) ?>">
			<h3<?php $this->html( 'userlangattributes' ) ?> id="<?php echo htmlspecialchars( $labelId )
				?>"><?php
				echo htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $msg );
				?></h3>
			<div class="body">
				<?php
				if ( is_array( $content ) ) {
				?>
				<ul>
					<?php
					foreach ( $content as $key => $val ) {
						echo $this->makeListItem( $key, $val );
					}
					if ( $hook !== null ) {
						// Avoid PHP 7.1 warning
						$skin = $this;
						Hooks::run( $hook, [ &$skin, true ] );
					}
					?>
				</ul>
				<?php
				} else {
					// Allow raw HTML block to be defined by extensions
					echo $content;
				}

				$this->renderAfterPortlet( $name );
				?>
			</div>
		</div>
	<?php
	}

	/**
	 * Render one or more navigations elements by name, automatically reversed by css
	 * when UI is in RTL mode
	 *
	 * @param TemplateParser $templateParser
	 * @param array $elements
	 */
	protected function renderNavigation( TemplateParser $templateParser, array $elements ) {
		// Render elements
		foreach ( $elements as $name => $element ) {
			switch ( $element ) {
				case 'NAMESPACES':
					$this->renderNamespacesComponent( $templateParser );
					break;
				case 'VARIANTS':
					$this->renderVariantsComponent();
					break;
				case 'VIEWS':
					$this->renderViewsComponent( $templateParser );
					break;
				case 'ACTIONS':
					$this->renderActionsComponent();
					break;
				case 'PERSONAL':
					$this->renderPersonalComponent( $templateParser );
					break;
				case 'SEARCH':
					$this->renderSearchComponent( $templateParser );
					break;
			}
		}
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
	 * @param TemplateParser $templateParser
	 */
	private function renderNamespacesComponent( TemplateParser $templateParser ) {
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
		};

		echo $templateParser->processTemplate( 'VectorTabs', $props );
	}

	private function renderVariantsComponent() {
		?>
		<div id="p-variants" role="navigation" class="vectorMenu<?php
		if ( count( $this->data['variant_urls'] ) == 0 ) {
			echo ' emptyPortlet';
		}
		?>" aria-labelledby="p-variants-label">
			<?php
			// Replace the label with the name of currently chosen variant, if any
			$variantLabel = $this->getMsg( 'variants' )->text();
			foreach ( $this->data['variant_urls'] as $item ) {
				if ( isset( $item['class'] ) && stripos( $item['class'], 'selected' ) !== false ) {
					$variantLabel = $item['text'];
					break;
				}
			}
			?>
			<input type="checkbox" class="vectorMenuCheckbox" aria-labelledby="p-variants-label" />
			<h3 id="p-variants-label">
				<span><?php echo htmlspecialchars( $variantLabel ) ?></span>
			</h3>
			<ul class="menu">
				<?php
				foreach ( $this->data['variant_urls'] as $key => $item ) {
					echo $this->makeListItem( $key, $item );
				}
				?>
			</ul>
		</div>
		<?php
	}

	/**
	 * @param TemplateParser $templateParser
	 */
	private function renderViewsComponent( TemplateParser $templateParser ) {
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
		};

		echo $templateParser->processTemplate( 'VectorTabs', $props );
	}

	private function renderActionsComponent() {
		?>
		<div id="p-cactions" role="navigation" class="vectorMenu<?php
		if ( count( $this->data['action_urls'] ) == 0 ) {
			echo ' emptyPortlet';
		}
		?>" aria-labelledby="p-cactions-label">
			<input type="checkbox" class="vectorMenuCheckbox" aria-labelledby="p-cactions-label" />
			<h3 id="p-cactions-label"><span><?php
				$this->msg( 'vector-more-actions' )
			?></span></h3>
			<ul class="menu"<?php $this->html( 'userlangattributes' ) ?>>
				<?php
				foreach ( $this->data['action_urls'] as $key => $item ) {
					echo $this->makeListItem( $key, $item );
				}
				?>
			</ul>
		</div>
		<?php
	}

	/**
	 * @param TemplateParser $templateParser
	 */
	private function renderPersonalComponent( TemplateParser $templateParser ) {
		$personalTools = $this->getPersonalTools();
		$props = [
			'empty-portlet' => ( count( $this->data['personal_urls'] ) == 0 ) ? 'emptyPortlet' : '',
			'msg-label' => $this->getMsg( 'personaltools' )->text(),
			'html-userlangattributes' => $this->html( 'userlangattributes' ),
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
		};

		if ( array_key_exists( 'uls', $personalTools ) ) {
			$props['html-lang-selector'] = $this->makeListItem( 'uls', $personalTools[ 'uls' ] );
			unset( $personalTools[ 'uls' ] );
		}

		foreach ( $personalTools as $key => $item ) {
			$props['html-personal-tools'] .= $this->makeListItem( $key, $item );
		}

		echo $templateParser->processTemplate( 'PersonalMenu', $props );
	}

	/**
	 * @param TemplateParser $templateParser
	 */
	private function renderSearchComponent( TemplateParser $templateParser ) {
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
		echo $templateParser->processTemplate( 'SearchBox', $props );
	}
}
