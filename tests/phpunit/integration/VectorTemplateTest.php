<?php
namespace MediaWiki\Skins\Vector\Tests\Integration;

use GlobalVarConfig;
use MediaWikiIntegrationTestCase;
use TemplateParser;
use VectorTemplate;
use Wikimedia\TestingAccessWrapper;

/**
 * Class VectorTemplateTest
 * @package MediaWiki\Skins\Vector\Tests\Unit
 * @group Vector
 * @group Skins
 *
 * @coversDefaultClass \VectorTemplate
 */
class VectorTemplateTest extends MediaWikiIntegrationTestCase {

	/**
	 * @return \VectorTemplate
	 */
	private function provideVectorTemplateObject() {
		$template = new VectorTemplate(
			GlobalVarConfig::newInstance(),
			new TemplateParser(),
			true
		);
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
	 * @covers ::makeListItem
	 */
	public function testMakeListItemRespectsCollapsibleOption() {
		$template = $this->provideVectorTemplateObject();
		$listItemClass = 'my_test_class';
		$options = [ 'vector-collapsible' => true ];
		$item = [ 'class' => $listItemClass ];
		$nonCollapsible = $template->makeListItem( 'key', $item, [] );
		$collapsible = $template->makeListItem( 'key', [], $options );

		$this->assertTrue(
			$this->expectNodeAttribute( $collapsible, 'li', 'class', 'collapsible' ),
			'The collapsible element has to have `collapsible` class'
		);
		$this->assertFalse(
			$this->expectNodeAttribute( $nonCollapsible, 'li', 'class', 'collapsible' ),
			'The non-collapsible element should not have `collapsible` class'
		);
		$this->assertTrue(
			$this->expectNodeAttribute( $nonCollapsible, 'li', 'class', $listItemClass ),
			'The non-collapsible element should preserve item class'
		);
	}

	/**
	 * @covers ::makeListItem
	 */
	public function testWatcAndUnwatchHasIconClass() {
		$template = $this->provideVectorTemplateObject();
		$this->setMwGlobals( [
			'wgVectorUseIconWatch' => true
		] );
		$listItemClass = 'my_test_class';
		$options = [];
		$item = [ 'class' => $listItemClass ];

		$watchListItem = $template->makeListItem( 'watch', $item, [] );
		$unwatchListItem = $template->makeListItem( 'unwatch', [], $options );
		$regularListItem = $template->makeListItem( 'whatever', $item, $options );

		$this->assertTrue(
			$this->expectNodeAttribute( $watchListItem, 'li', 'class', 'icon' ),
			'Watch list items require an "icon" class'
		);
		$this->assertTrue(
			$this->expectNodeAttribute( $unwatchListItem, 'li', 'class', 'icon' ),
			'Unwatch list items require an "icon" class'
		);
		$this->assertFalse(
			$this->expectNodeAttribute( $regularListItem, 'li', 'class', 'icon' ),
			'List item other than watch or unwatch should not have an "icon" class'
		);
		$this->assertTrue(
			$this->expectNodeAttribute( $watchListItem, 'li', 'class', $listItemClass ),
			'Watch list items require an item class'
		);
	}

	/**
	 * @covers ::makeListItem
	 */
	public function testWatchAndUnwatchHasIconClassOnlyIfVectorUseIconWatchIsSet() {
		$template = $this->provideVectorTemplateObject();
		$this->setMwGlobals( [
			'wgVectorUseIconWatch' => false
		] );
		$listItemClass = 'my_test_class';
		$item = [ 'class' => $listItemClass ];

		$watchListItem = $template->makeListItem( 'watch', $item, [] );

		$this->assertFalse(
			$this->expectNodeAttribute( $watchListItem, 'li', 'class', 'icon' ),
			'Watch list should not have an "icon" class when VectorUserIconWatch is disabled'
		);
		$this->assertTrue(
			$this->expectNodeAttribute( $watchListItem, 'li', 'class', $listItemClass ),
			'Watch list items require an item class'
		);
	}

	/**
	 * @covers ::buildViewsProps
	 * @covers ::buildActionsProps
	 * @covers ::buildVariantsProps
	 * @covers ::buildNamespacesProps
	 * @covers ::getMenuProps
	 */
	public function testGetMenuProps() {
		$langAttrs = 'LANG_ATTRIBUTES';
		$vectorTemplate = $this->provideVectorTemplateObject();
		$vectorTemplate->set( 'view_urls', [] );
		$vectorTemplate->set( 'personal_urls', [] );
		$vectorTemplate->set( 'skin', new \SkinVector() );
		$vectorTemplate->set( 'userlangattributes', $langAttrs );
		$openVectorTemplate = TestingAccessWrapper::newFromObject( $vectorTemplate );

		$props = $openVectorTemplate->getMenuProps();
		$views = $openVectorTemplate->buildViewsProps();
		$namespaces = $openVectorTemplate->buildNamespacesProps();
		$this->assertSame( $views, [
			'id' => 'p-views',
			'class' => 'emptyPortlet vectorTabs',
			'label-id' => 'p-views-label',
			'label' => 'Views',
			'html-userlangattributes' => $langAttrs,
			'html-items' => '',
			'class' => 'emptyPortlet vectorTabs',
		] );

		$variants = $openVectorTemplate->buildVariantsProps();
		$actions = $openVectorTemplate->buildActionsProps();
		$this->assertSame( $namespaces['class'],
			'emptyPortlet vectorTabs' );
		$this->assertSame( $variants['class'],
			'emptyPortlet vectorMenu' );
		$this->assertSame( $actions['class'],
			'emptyPortlet vectorMenu' );
		$this->assertSame( $props['data-personal-menu']['class'],
			'emptyPortlet' );
	}

}
