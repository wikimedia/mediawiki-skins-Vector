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

use Vector\FeatureManagement\Requirements\LatestSkinVersionRequirement;

/**
 * @group Vector
 * @coversDefaultClass \Vector\FeatureManagement\Requirements\LatestSkinVersionRequirement
 */
class LatestSkinVersionRequirementTest extends \MediaWikiTestCase {

	/**
	 * @covers ::isMet
	 * @covers ::getVersion
	 */
	public function testRequest() {
		$request = $this->getMockBuilder( \WebRequest::class )->getMock();
		$request
			->expects( $this->exactly( 1 ) )
			->method( 'getVal' )
			->with( $this->anything(), $this->equalTo( '1' ) )
			->willReturn( 'beta' );

		$user = $this->createMock( \User::class );
		$user
			->expects( $this->exactly( 1 ) )
			->method( 'getOption' )
			->with( $this->anything(), $this->equalTo( '2' ) )
			->willReturn( '1' );

		$config = new \HashConfig( [ 'VectorDefaultSkinVersion' => '2' ] );

		$requirement = new LatestSkinVersionRequirement( $request, $user, $config );

		$this->assertFalse(
			$requirement->isMet(),
			'WebRequest::getVal takes precedence. "beta" isn\'t considered latest.'
		);
	}

	/**
	 * @covers ::isMet
	 * @covers ::getVersion
	 */
	public function testUserPreference() {
		$request = new WebRequest();

		$user = $this->createMock( \User::class );
		$user
			->expects( $this->exactly( 1 ) )
			->method( 'getOption' )
			->with( $this->anything(), $this->equalTo( '2' ) )
			->willReturn( '1' );

		$config = new \HashConfig( [ 'VectorDefaultSkinVersion' => '2' ] );

		$requirement = new LatestSkinVersionRequirement( $request, $user, $config );

		$this->assertFalse(
			$requirement->isMet(),
			'User preference takes second place. "1" (legacy) isn\'t considered latest.'
		);
	}

	/**
	 * @covers ::isMet
	 * @covers ::getVersion
	 */
	public function testConfig() {
		$request = new WebRequest();
		$user = \MediaWikiTestCase::getTestUser()
			->getUser();

		$config = new HashConfig( [ 'VectorDefaultSkinVersion' => '2' ] );

		$requirement = new LatestSkinVersionRequirement( $request, $user, $config );

		$this->assertTrue(
			$requirement->isMet(),
			'Config takes third place. "2" is considered latest.'
		);
	}
}
