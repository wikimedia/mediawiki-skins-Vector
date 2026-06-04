<?php
/**
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
 * @since 1.35
 */

namespace MediaWiki\Skins\Vector\Tests\Unit\Components;

use MediaWiki\Language\MessageLocalizer;
use MediaWiki\Message\Message;
use MediaWiki\Skins\Vector\Components\VectorComponentPageToolbar;
use MediaWiki\Skins\Vector\FeatureManagement\FeatureManager;
use ReflectionMethod;

/**
 * @group Vector
 * @group Components
 * @coversDefaultClass \MediaWiki\Skins\Vector\Components\VectorComponentPageToolbar
 */
class VectorComponentPageToolbarTest extends \MediaWikiUnitTestCase {
	private const MAIN = [
		'id' => 'p-navigation',
	];
	private const SUPPORT = [
		'id' => 'p-support',
	];
	private const TOOLBOX = [
		'id' => 'p-tb',
	];
	private const WIKIBASE = [
		'id' => 'p-wikibase-otherprojects',
	];

	public static function provideExtractPageToolsFromSidebar() {
		return [
			[
				[],
				[], [],
				'No change if sidebar is missing keys'
			],
			[
				[
					'data-portlets-first' => self::MAIN,
					'array-portlets-rest' => [
						self::SUPPORT
					],
				],
				[
					'data-portlets-first' => self::MAIN,
					'array-portlets-rest' => [
						self::SUPPORT
					],
				],
				[],
				'No change if no toolbox found'
			],
			[
				[
					'data-portlets-first' => self::TOOLBOX,
					'array-portlets-rest' => [ self::SUPPORT ],
				],
				[
					'data-portlets-first' => self::TOOLBOX,
					'array-portlets-rest' => [ self::SUPPORT ],
				],
				[],
				'A toolbox in first part of sidebar is ignored.'
			],

			[
				[
					'data-portlets-first' => self::MAIN,
					'array-portlets-rest' => [ self::SUPPORT, self::TOOLBOX, self::WIKIBASE ],
				],
				// new expected sidebar
				[
					'data-portlets-first' => self::MAIN,
					'array-portlets-rest' => [
						self::SUPPORT
					],
				],
				// new expected page tools menu
				[
					self::TOOLBOX, self::WIKIBASE
				],
				'Toolbox and any items after it are pulled out.'
			],
		];
	}

	/**
	 * @covers ::extractPageToolsFromSidebar
	 * @dataProvider provideExtractPageToolsFromSidebar
	 */
	public function testExtractPageToolsFromSidebar( $sidebar, $expectedSidebar, $expectedPageTools, $msg ) {
		$pageTools = [];
		$extractPageToolsFromSidebar = new ReflectionMethod(
			VectorComponentPageToolbar::class,
			'extractPageToolsFromSidebar'
		);
		$extractPageToolsFromSidebar->invokeArgs( null, [ &$sidebar, &$pageTools ] );
		$this->assertEquals( $expectedSidebar, $sidebar );
		$this->assertEquals( $expectedPageTools, $pageTools, $msg );
	}

	public static function provideGetTemplateData() {
		$iconOnlyClass = ' cdx-button cdx-button--fake-button cdx-button--fake-button--enabled '
			. 'cdx-button--weight-quiet cdx-button--icon-only';
		return [
			[
				[],
				[],
				true,
				[]
			],
			[
				[
					'data-views' => [
						'id' => 'p-views',
						'class' => 'foo',
						'array-items' => [
							[
								'id' => 'ca-edit',
								'class' => '',
								'array-links' => [
									[
										'array-attributes' => [],
										'text' => 'edit',
									]
								],
							],
							[
								'id' => 'ca-unwatch',
								'class' => '',
								'array-links' => [
									[
										'icon' => 'unStar',
										'array-attributes' => [],
										'text' => 'watch',
									]
								],
							],
							[
								'id' => 'ca-bookmark',
								'class' => '',
								'array-links' => [
									[
										'array-attributes' => [
											[
												'key' => 'class',
												'value' => '',
											]
										],
										'icon' => 'bookmark',
										'text' => 'bookmark',
									]
								],
							],
						]
					],
				],
				[],
				true,
				[
					[
						'id' => 'ca-edit',
						'class' => 'user-links-collapsible-item vector-menu-item--collapsible vector-tab-noicon',
						'array-links' => [
							[
								'array-attributes' => [],
								'text' => 'edit',
								'icon' => false,
							]
						],
					],
					[
						'id' => 'ca-unwatch',
						'class' => 'vector-tab-noicon',
						'array-links' => [
							[
								'icon' => 'unStar',
								'array-attributes' => [],
								'text' => 'watch',
							]
						],
					],
					[
						'id' => 'ca-bookmark',
						'class' => '',
						'array-links' => [
							[
								'array-attributes' => [
									[
										'key' => 'class',
										'value' => $iconOnlyClass,
									]
								],
								'icon' => 'bookmark',
								'text' => 'bookmark'
							]
						],
					]
				]
			]
		];
	}

	/**
	 * @covers ::getTemplateData
	 * @dataProvider provideGetTemplateData
	 */
	public function testGetTemplateData( $portletData, $sidebar, $isFeatureEnabled, $expectedToolbarActions ) {
		$localizer = $this->createMock( MessageLocalizer::class );
		$localizer->method( 'msg' )->willReturnCallback( function ( $key, ...$params ) {
			$msg = $this->createMock( Message::class );
			$msg->method( '__toString' )->willReturn( $key );
			$msg->method( 'text' )->willReturn( $key );
			return $msg;
		} );
		$featureManager = $this->createMock( FeatureManager::class );
		$featureManager->method( 'isFeatureEnabled' )->willReturn( $isFeatureEnabled );
		$vectorComponentPageToolbar = new VectorComponentPageToolbar(
			$localizer,
			$featureManager,
			$portletData,
			$sidebar
		);
		$data = $vectorComponentPageToolbar->getTemplateData();
		$this->assertArrayHasKey( 'data-page-tools', $data );
		$this->assertArrayHasKey( 'data-portlets', $data );
		$this->assertArrayHasKey( 'data-page-tools-dropdown', $data );
		$this->assertSame(
			$expectedToolbarActions,
			$data[ 'data-toolbar-actions' ]['array-list-items'] ?? []
		);
	}
}
