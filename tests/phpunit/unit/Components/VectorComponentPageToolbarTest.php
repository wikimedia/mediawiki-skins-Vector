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
class VectorComponentPageToolbarTest extends VectorComponentSnapshotTestCase {
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

	public static function provideMoveWatchLinkToViews() {
		return [
			[
				// Test case: No actions data
				[
					'array-items' => [
						[ 'name' => 'edit' ],
						[ 'name' => 'history' ],
					]
				],
				[
					'array-items' => []
				],
				'move-watch-link-views-no-actions.json',
				'move-watch-link-actions-no-actions.json',
			],
			[
				// Test case: Watch link exists in actions data
				[
					'array-items' => [
						[ 'name' => 'edit' ],
						[ 'name' => 'history' ],
					]
				],
				[
					'array-items' => [
						[ 'name' => 'watch' ]
					]
				],
				'move-watch-link-views-watch.json',
				'move-watch-link-actions-watch.json',
			], [
				// Test case: Unwatch link exists in actions data
				[
					'array-items' => [
						[ 'name' => 'edit' ],
						[ 'name' => 'history' ],
					]
				],
				[
					'array-items' => [
						[ 'name' => 'unwatch' ]
					]
				],
				'move-watch-link-views-unwatch.json',
				'move-watch-link-actions-unwatch.json',
			],
			[
				// Test case: No watch/unwatch links in actions data
				[
					'array-items' => [
						[ 'name' => 'edit' ],
						[ 'name' => 'history' ],
					]
				],
				[
					'array-items' => [
						[ 'name' => 'delete' ],
					]
				],
				'move-watch-link-views-no-watch-and-delete.json',
				'move-watch-link-actions-no-watch-and-delete.json',
			]
		];
	}

	/**
	 * @covers ::moveWatchLinkToViews
	 * @dataProvider provideMoveWatchLinkToViews
	 */
	public function testMoveWatchLinkToViews(
		$viewsData, $actionsData, $expectedViewsSnapshot, $expectedActionsSnapshot
	) {
		$moveWatchLinkToViews = new ReflectionMethod(
			VectorComponentPageToolbar::class,
			'moveWatchLinkToViews'
		);
		$moveWatchLinkToViews->invokeArgs( null, [ &$viewsData, &$actionsData ] );
		$this->assertEqualsSnapshot( $expectedViewsSnapshot, $viewsData );
		$this->assertEqualsSnapshot( $expectedActionsSnapshot, $actionsData );
	}

	public static function provideMoveWatchLinkToViewsPosition() {
		$emptyActions = [ 'array-items' => [], 'class' => ' emptyPortlet' ];
		return [
			'insert into empty views' => [
				[],
				[ [ 'name' => 'watch' ] ],
				[ 'array-items' => [ [ 'name' => 'watch' ] ] ],
				$emptyActions,
			],
			'append when nothing follows' => [
				[ [ 'name' => 'create' ] ],
				[ [ 'name' => 'watch' ] ],
				[ 'array-items' => [ [ 'name' => 'create' ], [ 'name' => 'watch' ] ] ],
				$emptyActions,
			],
			'insert before bookmark' => [
				[ [ 'name' => 'create' ], [ 'name' => 'bookmark' ] ],
				[ [ 'name' => 'watch' ] ],
				[ 'array-items' => [ [ 'name' => 'create' ], [ 'name' => 'watch' ], [ 'name' => 'bookmark' ] ] ],
				$emptyActions,
			],
			'other actions stay when watch moves' => [
				[ [ 'name' => 'create' ] ],
				[ [ 'name' => 'delete' ], [ 'name' => 'watch' ] ],
				[ 'array-items' => [ [ 'name' => 'create' ], [ 'name' => 'watch' ] ] ],
				[ 'array-items' => [ [ 'name' => 'delete' ] ] ],
			],
			'history wins over bookmark' => [
				[ [ 'name' => 'edit' ], [ 'name' => 'history' ], [ 'name' => 'bookmark' ] ],
				[ [ 'name' => 'watch' ] ],
				[ 'array-items' => [
					[ 'name' => 'edit' ], [ 'name' => 'history' ], [ 'name' => 'watch' ], [ 'name' => 'bookmark' ]
				] ],
				$emptyActions,
			],
		];
	}

	/**
	 * @covers ::findWatchstarInsertIndex
	 * @covers ::moveWatchLinkToViews
	 * @dataProvider provideMoveWatchLinkToViewsPosition
	 */
	public function testMoveWatchLinkToViewsPosition(
		array $viewItems, array $actionItems, array $expectedViewsData, array $expectedActionsData
	) {
		$viewsData = [
			'array-items' => $viewItems,
		];
		$actionsData = [
			'array-items' => $actionItems,
		];
		$moveWatchLinkToViews = new ReflectionMethod(
			VectorComponentPageToolbar::class,
			'moveWatchLinkToViews'
		);
		$moveWatchLinkToViews->invokeArgs( null, [ &$viewsData, &$actionsData ] );
		$this->assertSame( $expectedViewsData, $viewsData );
		$this->assertSame( $expectedActionsData, $actionsData );
	}

	public static function provideExtractToolboxFromSidebar() {
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
	 * @covers ::extractToolboxFromSidebar
	 * @dataProvider provideExtractToolboxFromSidebar
	 */
	public function testExtractToolboxFromSidebar( $sidebar, $expectedSidebar, $expectedPageTools, $msg ) {
		$pageTools = [];
		$extractToolboxFromSidebar = new ReflectionMethod(
			VectorComponentPageToolbar::class,
			'extractToolboxFromSidebar'
		);
		$extractToolboxFromSidebar->invokeArgs( null, [ &$sidebar, &$pageTools ] );
		$this->assertEquals( $expectedSidebar, $sidebar );
		$this->assertEquals( $expectedPageTools, $pageTools, $msg );
	}

	public static function provideGetTemplateData() {
		return [
			[
				[],
				[],
				true,
				'page-toolbar-1.json'
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
				'page-toolbar-2.json'
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
								'id' => 'ca-addsection',
								'class' => '',
								'array-links' => [
									[
										'array-attributes' => [],
										'text' => 'edit',
									]
								],
							],
						],
					],
				],
				[],
				true,
				'page-toolbar-move-ca-addsection.json',
				true
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
								'id' => 'ca-addsection',
								'class' => '',
								'array-links' => [
									[
										'icon' => 'bellOutline',
										'array-attributes' => [],
										'text' => 'edit',
									]
								],
							],
						],
					],
				],
				[],
				true,
				'page-toolbar-dontmove-ca-addsection.json',
				false
			],
			[
				[
					'data-views' => [
						'id' => 'p-views',
						'class' => 'foo',
						'array-items' => [],
					],
					'data-actions' => [
						'id' => 'p-cactions',
						'array-items' => [],
					],
				],
				[],
				false,
				'page-toolbar-specialpage.json',
				false
			]
		];
	}

	/**
	 * @covers ::getTemplateData
	 * @dataProvider provideGetTemplateData
	 */
	public function testGetTemplateData(
		array $portletData,
		array $sidebar,
		bool $isFeatureEnabled,
		string $snapshotName,
		bool $hasAddTopic = false
	) {
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
			$sidebar,
			$hasAddTopic
		);
		$data = $vectorComponentPageToolbar->getTemplateData();
		$this->assertEqualsSnapshot(
			$snapshotName,
			$data
		);
	}
}
