<?php
namespace MediaWiki\Skins\Vector\Tests\Integration;

use MediaWikiIntegrationTestCase;
use RequestContext;
use SkinVector;
use Title;
use Wikimedia\TestingAccessWrapper;

/**
 * Class VectorTemplateTest
 * @package MediaWiki\Skins\Vector\Tests\Unit
 * @group Vector
 * @group Skins
 *
 * @coversDefaultClass \SkinVector
 */
class SkinVectorTest extends MediaWikiIntegrationTestCase {

	/**
	 * @return \VectorTemplate
	 */
	private function provideVectorTemplateObject() {
		$template = new SkinVector( [ 'name' => 'vector' ] );
		return $template;
	}

	/**
	 * @param string $nodeString an HTML of the node we want to verify
	 * @param string $tag Tag of the element we want to check
	 * @param string $attribute Attribute of the element we want to check
	 * @param string $search Value of the attribute we want to verify
	 * @return bool
	 */
	private function expectNodeAttribute( $nodeString, $tag, $attribute, $search ) {
		$node = new \DOMDocument();
		$node->loadHTML( $nodeString );
		$element = $node->getElementsByTagName( $tag )->item( 0 );
		if ( !$element ) {
			return false;
		}

		$values = explode( ' ', $element->getAttribute( $attribute ) );
		return in_array( $search, $values );
	}

	/**
	 * @covers ::getMenuProps
	 */
	public function testGetMenuProps() {
		$title = Title::newFromText( 'SkinVector' );
		$context = RequestContext::getMain();
		$context->setTitle( $title );
		$context->setLanguage( 'fr' );
		$vectorTemplate = $this->provideVectorTemplateObject();
		$this->setTemporaryHook( 'PersonalUrls', [
			function ( &$personal_urls, &$title, $skin ) {
				$personal_urls = [];
			}
		] );
		$this->setTemporaryHook( 'SkinTemplateNavigation', [
			function ( &$skinTemplate, &$content_navigation ) {
				$content_navigation = [
					'actions' => [],
					'namespaces' => [],
					'variants' => [],
					'views' => [],
				];
			}
		] );
		$openVectorTemplate = TestingAccessWrapper::newFromObject( $vectorTemplate );

		$props = $openVectorTemplate->getMenuProps();
		$views = $props['data-page-actions'];
		$namespaces = $props['data-namespace-tabs'];

		$this->assertSame(
			[
				// Provided by core
				'id' => 'p-views',
				'class' => 'mw-portlet mw-portlet-views emptyPortlet vector-menu vector-menu-tabs vectorTabs',
				'html-tooltip' => '',
				'html-items' => '',
				'html-after-portal' => '',
				'label' => $context->msg( 'views' )->text(),

				// provided by VECTOR
				'label-id' => 'p-views-label',
				'list-classes' => 'vector-menu-content-list',
				'is-dropdown' => false,
			],
			$views
		);

		$variants = $props['data-variants'];
		$actions = $props['data-page-actions-more'];
		$this->assertSame(
			'mw-portlet mw-portlet-namespaces emptyPortlet vector-menu vector-menu-tabs vectorTabs',
			$namespaces['class']
		);
		$this->assertSame(
			'mw-portlet mw-portlet-variants emptyPortlet vector-menu vector-menu-dropdown vectorMenu',
			$variants['class']
		);
		$this->assertSame(
			'mw-portlet mw-portlet-cactions emptyPortlet vector-menu vector-menu-dropdown vectorMenu',
			$actions['class']
		);
		$this->assertSame(
			'mw-portlet mw-portlet-personal emptyPortlet vector-menu',
			$props['data-personal-menu']['class']
		);
	}

}
