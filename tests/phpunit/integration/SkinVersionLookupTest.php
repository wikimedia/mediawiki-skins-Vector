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

use Vector\SkinVersionLookup;

/**
 * @group Vector
 * @coversDefaultClass \Vector\SkinVersionLookup
 */
class SkinVersionLookupTest extends \MediaWikiTestCase {
	/**
	 * @covers ::isLegacy
	 * @covers ::getVersion
	 */
	public function testRequest() {
		$request = $this->getMockBuilder( \WebRequest::class )->getMock();
		$request
			->expects( $this->exactly( 2 ) )
			->method( 'getVal' )
			->with( $this->anything(), $this->equalTo( '1' ) )
			->willReturn( 'beta' );

		$user = $this->createMock( \User::class );
		$user
			->expects( $this->exactly( 2 ) )
			->method( 'getOption' )
			->with( $this->anything(), $this->equalTo( '2' ) )
			->willReturn( '1' );

		$config = new HashConfig( [ 'VectorDefaultSkinVersion' => '2' ] );

		$skinVersionLookup = new SkinVersionLookup( $request, $user, $config );

		$this->assertSame(
			$skinVersionLookup->getVersion(),
			'beta',
			'Query parameter is the first priority.'
		);
		$this->assertSame(
			$skinVersionLookup->isLegacy(),
			false,
			'Version is non-legacy.'
		);
	}

	/**
	 * @covers ::getVersion
	 * @covers ::isLegacy
	 */
	public function testUserPreference() {
		$request = $this->getMockBuilder( \WebRequest::class )->getMock();
		$request
			->expects( $this->exactly( 2 ) )
			->method( 'getVal' )
			->with( $this->anything(), $this->equalTo( '1' ) )
			->willReturn( '1' );

		$user = $this->createMock( \User::class );
		$user
			->expects( $this->exactly( 2 ) )
			->method( 'getOption' )
			->with( $this->anything(), $this->equalTo( '2' ) )
			->willReturn( '1' );

		$config = new HashConfig( [ 'VectorDefaultSkinVersion' => '2' ] );

		$skinVersionLookup = new SkinVersionLookup( $request, $user, $config );

		$this->assertSame(
			$skinVersionLookup->getVersion(),
			'1',
			'User preference is the second priority.'
		);
		$this->assertSame(
			$skinVersionLookup->isLegacy(),
			true,
			'Version is legacy.'
		);
	}

	/**
	 * @covers ::getVersion
	 * @covers ::isLegacy
	 */
	public function testConfig() {
		$request = $this->getMockBuilder( \WebRequest::class )->getMock();
		$request
			->expects( $this->exactly( 2 ) )
			->method( 'getVal' )
			->with( $this->anything(), $this->equalTo( '2' ) )
			->willReturn( '2' );

		$user = $this->createMock( \User::class );
		$user
			->expects( $this->exactly( 2 ) )
			->method( 'getOption' )
			->with( $this->anything(), $this->equalTo( '2' ) )
			->willReturn( '2' );

		$config = new HashConfig( [ 'VectorDefaultSkinVersion' => '2' ] );

		$skinVersionLookup = new SkinVersionLookup( $request, $user, $config );

		$this->assertSame(
			$skinVersionLookup->getVersion(),
			'2',
			'Config is the third priority.'
		);
		$this->assertSame(
			$skinVersionLookup->isLegacy(),
			false,
			'Version is non-legacy.'
		);
	}
}
