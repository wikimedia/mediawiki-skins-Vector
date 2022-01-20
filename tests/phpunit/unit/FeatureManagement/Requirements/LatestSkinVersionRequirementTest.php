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

namespace Vector\FeatureManagement\Tests;

use MediaWiki\User\UserOptionsLookup;
use User;
use Vector\FeatureManagement\Requirements\LatestSkinVersionRequirement;
use WebRequest;

/**
 * @group Vector
 * @group FeatureManagement
 * @coversDefaultClass \Vector\FeatureManagement\Requirements\LatestSkinVersionRequirement
 */
class LatestSkinVersionRequirementTest extends \MediaWikiUnitTestCase {

	public function provideIsMet() {
		// $version, $expected, $msg
		yield 'not met' => [ 'vector', null, false, '"1" isn\'t considered latest.' ];
		yield 'met' => [ 'vector-2022', null, true, '"2" is considered latest.' ];
		yield 'met (useskin override)' => [ 'vector', 'vector-2022', true, 'useskin overrides' ];
		yield 'not met (useskin override)' => [ 'vector-2022', 'vector', false, 'useskin overrides' ];
	}

	/**
	 * @dataProvider provideIsMet
	 * @covers ::isMet
	 */
	public function testIsMet( $skin, $useSkin, $expected, $msg ) {
		$user = $this->createMock( User::class );
		$user->method( 'isRegistered' )->willReturn( true );
		$user->method( 'isSafeToLoad' )->willReturn( true );

		$userOptionsLookup = $this->createMock( UserOptionsLookup::class );
		$userOptionsLookup->method( 'getOption' )
			->willReturn( $skin );

		$request = $this->createMock( WebRequest::class );
		$request->method( 'getVal' )
			->willReturn( $useSkin );

		$requirement = new LatestSkinVersionRequirement(
			$request,
			$user,
			$userOptionsLookup
		);

		$this->assertSame( $expected, $requirement->isMet(), $msg );
	}

}
