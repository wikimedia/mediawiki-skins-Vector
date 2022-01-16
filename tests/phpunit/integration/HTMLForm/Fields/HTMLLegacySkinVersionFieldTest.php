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
 */

namespace MediaWiki\Skins\Vector\Tests\Integration;

use Vector\Constants;
use Vector\HTMLForm\Fields\HTMLLegacySkinVersionField;

/**
 * @group Vector
 * @coversDefaultClass \Vector\HTMLForm\Fields\HTMLLegacySkinVersionField
 */
class HTMLLegacySkinVersionFieldTest extends \MediaWikiIntegrationTestCase {

	public function provideDefault() {
		return [
			[ false, '0' ],
			[ false, false ],
			[ true, '1' ],
			[ true, true ],
		];
	}

	/**
	 * @dataProvider provideDefault
	 * @covers ::__construct
	 */
	public function testConstructValidatesDefault( $expected, $default ) {
		$field = new HTMLLegacySkinVersionField( [
			'default' => $default,
			'fieldname' => 'VectorSkinVersion',
		] );
		$this->assertSame(
			$expected,
			$field->getDefault()
		);
	}

	public function provideGetInput() {
		yield [ Constants::SKIN_VERSION_LEGACY, true ];
		yield [ Constants::SKIN_VERSION_LATEST, false ];
	}

	/**
	 * @dataProvider provideGetInput
	 * @covers ::getInputHTML
	 * @covers ::getInputOOUI
	 */
	public function testGetInput( $skinVersionValue, $checkValue ) {
		$params = [
			'fieldname' => 'VectorSkinVersion',
			'class' => HTMLLegacySkinVersionField::class,
			'section' => 'rendering/skin/skin-prefs',
			'label-message' => 'prefs-vector-enable-vector-1-label',
			'help-message' => 'prefs-vector-enable-vector-1-help',
			'default' => true,
			'hide-if' => [ '!==', 'skin', Constants::SKIN_NAME_LEGACY ],
		];
		$skinVersionField = new HTMLLegacySkinVersionField( $params );
		$checkField = new \HTMLCheckField( $params );

		$this->assertSame(
			$skinVersionField->getInputHTML( $skinVersionValue ),
			$checkField->getInputHTML( $checkValue ),
			'::getInputHTML matches HTMLCheckField::getInputHTML with mapped value'
		);

		$this->assertEquals(
			$skinVersionField->getInputOOUI( $skinVersionValue ),
			$checkField->getInputOOUI( $checkValue ),
			'::getInputOOUI matches HTMLCheckField::getInputOOUI with mapped value'
		);
	}

	public function provideLoadDataFromRequest() {
		yield [ null, Constants::SKIN_VERSION_LEGACY ];
		yield [ true, Constants::SKIN_VERSION_LEGACY ];
		yield [ false, Constants::SKIN_VERSION_LATEST ];
	}

	/**
	 * @dataProvider provideLoadDataFromRequest
	 * @covers ::loadDataFromRequest
	 */
	public function testLoadDataFromRequest( $wpVectorSkinVersion, $expectedResult ) {
		$skinVerionField = new HTMLLegacySkinVersionField( [
			'fieldname' => 'VectorSkinVersion',
			'default' => true,
		] );

		$request = new \WebRequest();
		$request->setVal( 'wpVectorSkinVersion', $wpVectorSkinVersion );

		$this->assertSame( $skinVerionField->loadDataFromRequest( $request ), $expectedResult );
	}
}
