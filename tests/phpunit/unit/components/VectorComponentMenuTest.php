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
 * @since 1.42
 */

namespace MediaWiki\Skins\Vector\Tests\Unit\Components;

use MediaWiki\Skins\Vector\Components\VectorComponent;
use MediaWiki\Skins\Vector\Components\VectorComponentMenu;
use MediaWikiUnitTestCase;

/**
 * @group Vector
 * @group Components
 * @coversDefaultClass \MediaWiki\Skins\Vector\Components\VectorComponentMenu
 */
class VectorComponentMenuTest extends MediaWikiUnitTestCase {
	/**
	 * @var array Menu item template data
	 */
	private static $arrayListItems = [
		[
			"html-item" => '<li><a>link1</a></li>',
			"name" => "link1",
			"html" => '<a>link1</a>',
			"id" => "link-1",
			"class" => "",
			"array-links" => [ [
				"icon" => "heart",
				"array-attributes" => [ [
					"key" => "href", "value" => ""
				], [
					"key" => "class", "value" => ""
				] ],
				"text" => "Link1"
			] ]
		],
		[
			"html-item" => '<li><a>link2</a></li>',
			"name" => "link2",
			"html" => '<a>link2</a>',
			"id" => "link-2",
			"class" => "",
			"array-links" => [ [
				"icon" => "userAdd",
				"array-attributes" => [ [
					"key" => "href", "value" => ""
				], [
					"key" => "class", "value" => ""
				] ],
				"text" => "Link2"
			] ]
		]
	];

	/**
	 * @return array[]
	 */
	public static function provideCountData(): array {
		return [
			[ [ "array-list-items" => self::$arrayListItems ], 2 ],
			[ [ "html-items" => '<li>Some item</li><li>Some item</li><li>Some item</li>' ], 3 ]
		];
	}

	/**
	 * @return array[]
	 */
	public static function provideMenuData(): array {
		return [
			"Initializes data correctly" => [
				'data' => [ 'class' => 'some-class' ],
				'menuItemStyles' => [],
				'menuItemStyleOverrides' => [],
				'expectedData' => [
					'class' => 'some-class',
					'label' => '',
					'html-tooltip' => '',
					'label-class' => '',
					'html-before-portal' => '',
					'html-items' => '',
					'html-after-portal' => '',
					'array-list-items' => null,
				]
			],
			"Renders with html string" => [
				'data' => [
					'html-items' => '<li><a>link1</a></li><li><a>link2</a></li>',
					'array-list-items' => self::$arrayListItems
				],
				'menuItemStyles' => [],
				'menuItemStyleOverrides' => [],
				'expectedData' => [
					'class' => '',
					'label' => '',
					'html-tooltip' => '',
					'label-class' => '',
					'html-before-portal' => '',
					'html-items' => '<li><a>link1</a></li><li><a>link2</a></li>',
					'html-after-portal' => '',
					'array-list-items' => null
				],
			],
			"Renders with template data" => [
				'data' => [
					'html-items' => null, 'array-list-items' => self::$arrayListItems
				],
				'menuItemStyles' => [],
				'menuItemStyleOverrides' => [],
				'expectedData' => [
					'class' => '',
					'label' => '',
					'html-tooltip' => '',
					'label-class' => '',
					'html-before-portal' => '',
					'html-items' => '',
					'html-after-portal' => '',
					'array-list-items' => self::$arrayListItems
				]
			],
		];
	}

	/**
	 * @return array[]
	 */
	public static function provideUpdateMenuItemStylesData(): array {
		return [
			"Button styles" => [
				'menuItemStyles' => [ 'button' => true, 'collapsible' => true, 'icon' => 'star' ],
				'menuItemStyleOverrides' => [],
				'expectedData' => [ [
					"html-item" => '<li><a>link1</a></li>',
					"name" => "link1",
					"html" => '<a>link1</a>',
					"id" => "link-1",
					"class" => " " . VectorComponentMenu::COLLAPSIBLE_CLASS,
					"array-links" => [ [
						"icon" => 'star',
						"array-attributes" => [ [
							"key" => "href",
							"value" => ""
						], [
							"key" => "class",
							"value" => " " . VectorComponentMenu::BUTTON_CLASSES
						] ],
						"text" => "Link1"
					] ]
				], [
					"html-item" => '<li><a>link2</a></li>',
					"name" => "link2",
					"html" => '<a>link2</a>',
					"id" => "link-2",
					"class" => " " . VectorComponentMenu::COLLAPSIBLE_CLASS,
					"array-links" => [ [
						"icon" => 'star',
						"array-attributes" => [ [
							"key" => "href",
							"value" => ""
						], [
							"key" => "class",
							"value" => " " . VectorComponentMenu::BUTTON_CLASSES
						] ],
						"text" => "Link2"
					] ]
				] ]
			],
			"Button with iconOnly variation" => [
				'menuItemStyles' => [ 'button' => [ 'iconOnly' => true ] ],
				'menuItemStyleOverrides' => [],
				'expectedData' => [ [
					"html-item" => '<li><a>link1</a></li>',
					"name" => "link1",
					"html" => '<a>link1</a>',
					"id" => "link-1",
					"class" => "",
					"array-links" => [ [
						"icon" => 'heart',
						"array-attributes" => [ [
							"key" => "href",
							"value" => ""
						], [
							"key" => "class",
							"value" => " " . VectorComponentMenu::BUTTON_CLASSES .
								" " . VectorComponentMenu::ICON_ONLY_BUTTON_CLASS
						] ],
						"text" => "Link1"
					] ]
				], [
					"html-item" => '<li><a>link2</a></li>',
					"name" => "link2",
					"html" => '<a>link2</a>',
					"id" => "link-2",
					"class" => "",
					"array-links" => [ [
						"icon" => 'userAdd',
						"array-attributes" => [ [
							"key" => "href",
							"value" => ""
						], [
							"key" => "class",
							"value" => " " . VectorComponentMenu::BUTTON_CLASSES .
								" " . VectorComponentMenu::ICON_ONLY_BUTTON_CLASS
						] ],
						"text" => "Link2"
					] ]
				] ]
			],
			"Overrides applied to specific items" => [
				'menuItemStyles' => [ 'button' => true ],
				'menuItemStyleOverrides' => [
					'link-1' => [ 'button' => false, 'icon' => 'star' ]
				],
				'expectedData' => [ [
					"html-item" => '<li><a>link1</a></li>',
					"name" => "link1",
					"html" => '<a>link1</a>',
					"id" => "link-1",
					"class" => "",
					"array-links" => [ [
						"icon" => "star",
						"array-attributes" => [ [
							"key" => "href",
							"value" => ""
						], [
							"key" => "class",
							"value" => ""
						] ],
						"text" => "Link1"
					] ]
				], [
					"html-item" => '<li><a>link2</a></li>',
					"name" => "link2",
					"html" => '<a>link2</a>',
					"id" => "link-2",
					"class" => "",
					"array-links" => [ [
						"icon" => 'userAdd',
						"array-attributes" => [ [
							"key" => "href",
							"value" => ""
						], [
							"key" => "class",
							"value" => " " . VectorComponentMenu::BUTTON_CLASSES
						] ],
						"text" => "Link2"
					] ]
				] ]
			]
		];
	}

	/**
	 * This test checks if the VectorComponentMenu class can be instantiated
	 * @covers ::__construct
	 */
	public function testConstruct() {
		// Create a new VectorComponentMenu object
		$menu = new VectorComponentMenu( [] );

		// Check if the object is an instance of VectorComponent
		$this->assertInstanceOf( VectorComponent::class, $menu );
	}

	/**
	 * This test checks if the count method returns the correct number of items
	 * @covers ::count
	 * @dataProvider provideCountData
	 */
	public function testCount( array $data, int $expected ) {
		// Create a new VectorComponentMenu object
		$menu = new VectorComponentMenu( $data );

		// Check if the count method returns the correct number of items
		$this->assertSame( $expected, $menu->count() );
	}

	/**
	 * This test checks if the getTemplateData method returns the correct data
	 * @covers ::getTemplateData
	 * @dataProvider provideMenuData
	 */
	public function testGetTemplateData(
		array $data,
		array $menuItemStyles,
		array $menuItemStyleOverrides,
		array $expectedData
	 ) {
		// Create a new VectorComponentMenu object
		$menu = new VectorComponentMenu( $data, $menuItemStyles, $menuItemStyleOverrides );

		// Call the getTemplateData method
		$actualData = $menu->getTemplateData();

		// Check if the getTemplateData method returns the correct data
		$this->assertEqualsCanonicalizing( $expectedData, $actualData );
	}

	/**
	 * This test checks if the getTemplateData method returns the correct data
	 * @covers ::updateMenuItemStyles
	 * @dataProvider provideUpdateMenuItemStylesData
	 */
	public function testUpdateMenuItemStyles(
		array $menuItemStyles,
		array $menuItemStyleOverrides,
		array $expectedData
	) {
		$data = [
			'html-items' => null,
			'array-list-items' => self::$arrayListItems
		];
		$menu = new VectorComponentMenu( $data, $menuItemStyles, $menuItemStyleOverrides );
		$actualData = $menu->getTemplateData();

		$this->assertEqualsCanonicalizing( $expectedData, $actualData[ 'array-list-items' ] );
	}
}
