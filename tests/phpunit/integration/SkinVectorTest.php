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
	 * @return \SkinVector
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

	public function provideGetTocData() {
		$tocData = [
			'number-section-count' => 2,
			'array-sections' => [
				[
					'toclevel' => 1,
					'level' => '2',
					'line' => 'A',
					'number' => '1',
					'index' => '1',
					'fromtitle' => 'Test',
					'byteoffset' => 231,
					'anchor' => 'A',
					'array-sections' => [
						[
							'toclevel' => 2,
							'level' => '4',
							'line' => 'A1',
							'number' => '1.1',
							'index' => '2',
							'fromtitle' => 'Test',
							'byteoffset' => 245,
							'anchor' => 'A1'
						]
					]
				],
			]
		];

		return [
			// When zero sections
			[
				// $tocData
				[],
				// wgVectorTableOfContentsCollapseAtCount
				1,
				// expected 'vector-is-collapse-sections-enabled' value
				false
			],
			// When number of multiple sections is lower than configured value
			[
				// $tocData
				$tocData,
				// wgVectorTableOfContentsCollapseAtCount
				3,
				// expected 'vector-is-collapse-sections-enabled' value
				false
			],
			// When number of multiple sections is equal to the configured value
			[
				// $tocData
				$tocData,
				// wgVectorTableOfContentsCollapseAtCount
				2,
				// expected 'vector-is-collapse-sections-enabled' value
				true
			],
			// When number of multiple sections is higher than configured value
			[
				// $tocData
				$tocData,
				// wgVectorTableOfContentsCollapseAtCount
				1,
				// expected 'vector-is-collapse-sections-enabled' value
				true
			],
		];
	}

	/**
	 * @covers ::getTocData
	 * @dataProvider provideGetTOCData
	 */
	public function testGetTocData(
		array $tocData,
		int $configValue,
		bool $expected
	) {
		$this->setMwGlobals( [
			'wgVectorTableOfContentsCollapseAtCount' => $configValue
		] );

		$skinVector = new SkinVector( [ 'name' => 'vector-2022' ] );
		$openSkinVector = TestingAccessWrapper::newFromObject( $skinVector );
		$data = $openSkinVector->getTocData( $tocData );

		if ( empty( $tocData ) ) {
			$this->assertEquals( [], $data, 'toc data is empty when given an empty array' );
			return;
		}
		$this->assertArrayHasKey( 'vector-is-collapse-sections-enabled', $data );
		$this->assertEquals(
			$expected,
			$data['vector-is-collapse-sections-enabled'],
			'vector-is-collapse-sections-enabled has correct value'
		);
		$this->assertArrayHasKey( 'array-sections', $data );
	}

	/**
	 * @covers ::getTemplateData
	 */
	public function testGetTemplateData() {
		$title = Title::newFromText( 'SkinVector' );
		$context = RequestContext::getMain();
		$context->setTitle( $title );
		$context->setLanguage( 'fr' );
		$vectorTemplate = $this->provideVectorTemplateObject();
		$this->setTemporaryHook( 'PersonalUrls', [
			static function ( &$personal_urls, &$title, $skin ) {
				$personal_urls = [
					'pt-1' => [ 'text' => 'pt1' ],
				];
			}
		] );
		$this->setTemporaryHook( 'SkinTemplateNavigation::Universal', [
			static function ( &$skinTemplate, &$content_navigation ) {
				$content_navigation['actions'] = [
					'action-1' => []
				];
				$content_navigation['namespaces'] = [
					'ns-1' => []
				];
				$content_navigation['variants'] = [
					[
						'class' => 'selected',
						'text' => 'Language variant',
						'href' => '/url/to/variant',
						'lang' => 'zh-hant',
						'hreflang' => 'zh-hant',
					]
				];
				$content_navigation['views'] = [];
			}
		] );
		$openVectorTemplate = TestingAccessWrapper::newFromObject( $vectorTemplate );

		$props = $openVectorTemplate->getTemplateData()['data-portlets'];
		$views = $props['data-views'];
		$namespaces = $props['data-namespaces'];

		// The mediawiki core specification might change at any time
		// so let's limit the values we test to those we are aware of.
		$keysToTest = [
			'id', 'class', 'html-tooltip', 'html-items',
			'html-after-portal', 'html-before-portal',
			'label', 'heading-class', 'is-dropdown'
		];
		foreach ( $views as $key => $value ) {
			if ( !in_array( $key, $keysToTest ) ) {
				unset( $views[ $key] );
			}
		}
		$this->assertSame(
			[
				// Provided by core
				'id' => 'p-views',
				'class' => 'mw-portlet mw-portlet-views emptyPortlet vector-menu vector-menu-tabs',
				'html-tooltip' => '',
				'html-items' => '',
				'html-after-portal' => '',
				'html-before-portal' => '',
				'label' => $context->msg( 'views' )->text(),
				'heading-class' => 'vector-menu-heading',
				'is-dropdown' => false,
			],
			$views
		);

		$variants = $props['data-variants'];
		$actions = $props['data-actions'];
		$this->assertSame(
			'mw-portlet mw-portlet-namespaces vector-menu vector-menu-tabs',
			$namespaces['class']
		);
		$this->assertSame(
			'mw-portlet mw-portlet-variants vector-menu-dropdown-noicon vector-menu vector-menu-dropdown',
			$variants['class']
		);
		$this->assertSame(
			'mw-portlet mw-portlet-cactions vector-menu-dropdown-noicon vector-menu vector-menu-dropdown',
			$actions['class']
		);
		$this->assertSame(
			'mw-portlet mw-portlet-personal vector-user-menu-legacy vector-menu',
			$props['data-personal']['class']
		);
	}

}
