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

use MediaWiki\User\UserOptionsLookup;
use Vector\SkinVersionLookup;

/**
 * @group Vector
 * @coversDefaultClass \Vector\SkinVersionLookup
 */
class SkinVersionLookupTest extends \MediaWikiIntegrationTestCase {
	/**
	 * @covers ::isLegacy
	 * @covers ::getVersion
	 */
	public function testRequest() {
		$request = $this->getMockBuilder( \WebRequest::class )->getMock();
		$request
			->method( 'getVal' )
			->with( $this->anything(), 'beta' )
			->willReturn( 'alpha' );

		$user = $this->createMock( \User::class );
		$user
			->method( 'isRegistered' )
			->willReturn( false );

		$config = new HashConfig( [
			'VectorDefaultSkinVersion' => '2',
			'VectorDefaultSkinVersionForExistingAccounts' => '1'
		] );

		$userOptionsLookup = $this->getUserOptionsLookupMock( $user, '2', 'beta' );

		$skinVersionLookup = new SkinVersionLookup( $request, $user, $config, $userOptionsLookup );

		$this->assertSame(
			'alpha',
			$skinVersionLookup->getVersion(),
			'Query parameter is the first priority.'
		);
		$this->assertSame(
			false,
			$skinVersionLookup->isLegacy(),
			'Version is non-Legacy.'
		);
	}

	/**
	 * @covers ::getVersion
	 * @covers ::isLegacy
	 */
	public function testUserPreference() {
		$request = $this->getMockBuilder( \WebRequest::class )->getMock();
		$request
			->method( 'getVal' )
			->with( $this->anything(), 'beta' )
			->willReturn( 'beta' );

		$user = $this->createMock( \User::class );
		$user
			->method( 'isRegistered' )
			->willReturn( false );

		$config = new HashConfig( [
			'VectorDefaultSkinVersion' => '2',
			'VectorDefaultSkinVersionForExistingAccounts' => '1'
		] );

		$userOptionsLookup = $this->getUserOptionsLookupMock( $user, '2', 'beta' );

		$skinVersionLookup = new SkinVersionLookup( $request, $user, $config, $userOptionsLookup );

		$this->assertSame(
			'beta',
			$skinVersionLookup->getVersion(),
			'User preference is the second priority.'
		);
		$this->assertSame(
			false,
			$skinVersionLookup->isLegacy(),
			'Version is non-Legacy.'
		);
	}

	/**
	 * @covers ::getVersion
	 * @covers ::isLegacy
	 */
	public function testConfigRegistered() {
		$request = $this->getMockBuilder( \WebRequest::class )->getMock();
		$request
			->method( 'getVal' )
			->with( $this->anything(), '1' )
			->willReturn( '1' );

		$user = $this->createMock( \User::class );
		$user
			->method( 'isRegistered' )
			->willReturn( true );

		$config = new HashConfig( [
			'VectorDefaultSkinVersion' => '2',
			'VectorDefaultSkinVersionForExistingAccounts' => '1'
		] );

		$userOptionsLookup = $this->getUserOptionsLookupMock( $user, '1', '1' );

		$skinVersionLookup = new SkinVersionLookup( $request, $user, $config, $userOptionsLookup );

		$this->assertSame(
			'1',
			$skinVersionLookup->getVersion(),
			'Config is the third priority and distinguishes logged in users from anonymous users.'
		);
		$this->assertSame(
			true,
			$skinVersionLookup->isLegacy(),
			'Version is Legacy.'
		);
	}

	/**
	 * @covers ::getVersion
	 * @covers ::isLegacy
	 */
	public function testConfigAnon() {
		$request = $this->getMockBuilder( \WebRequest::class )->getMock();
		$request
			->method( 'getVal' )
			->with( $this->anything(), '2' )
			->willReturn( '2' );

		$user = $this->createMock( \User::class );
		$user
			->method( 'isRegistered' )
			->willReturn( false );

		$config = new HashConfig( [
			'VectorDefaultSkinVersion' => '2',
			'VectorDefaultSkinVersionForExistingAccounts' => '1'
		] );

		$userOptionsLookup = $this->getUserOptionsLookupMock( $user, '2', '2' );

		$skinVersionLookup = new SkinVersionLookup( $request, $user, $config, $userOptionsLookup );

		$this->assertSame(
			'2',
			$skinVersionLookup->getVersion(),
			'Config is the third priority and distinguishes anonymous users from logged in users.'
		);
		$this->assertSame(
			false,
			$skinVersionLookup->isLegacy(),
			'Version is non-Legacy.'
		);
	}

	/**
	 * @param User $user
	 * @param mixed|null $defaultOverride
	 * @param mixed|null $returnVal
	 * @return UserOptionsLookup
	 */
	private function getUserOptionsLookupMock( $user, $defaultOverride, $returnVal ) {
		$mock = $this->createMock( UserOptionsLookup::class );
		$mock->method( 'getOption' )
			->with( $user, $this->anything(), $defaultOverride )
			->willReturn( $returnVal );
		return $mock;
	}
}
