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

namespace Vector\FeatureManagement\Tests;

use Vector\FeatureManagement\FeatureManager;

/**
 * @group Vector
 * @group FeatureManagement
 * @coversDefaultClass \Vector\FeatureManagement\FeatureManager
 */
class FeatureManagerTest extends \MediaWikiUnitTestCase {

	/**
	 * @covers ::registerSet
	 */
	public function testRegisterSetThrowsWhenSetIsRegisteredTwice() {
		$this->expectException( \LogicException::class );

		$featureManager = new FeatureManager();
		$featureManager->registerSet( 'setA', true );
		$featureManager->registerSet( 'setA', true );
	}

	/**
	 * @covers ::registerSet
	 */
	public function testRegisterSetValidatesIsEnabled() {
		$this->expectException( \Wikimedia\Assert\ParameterAssertionException::class );

		$featureManager = new FeatureManager();
		$featureManager->registerSet( 'setA', 'foo' );
	}

	public static function provideInvalidFeatureConfig() {
		return [

			// ::registerFeature( string, int[] ) will throw an exception.
			[
				\Wikimedia\Assert\ParameterAssertionException::class,
				[ 1 ],
			],

			// The "bar" set hasn't been registered.
			[
				\InvalidArgumentException::class,
				[
					'bar',
				],
			],
		];
	}

	/**
	 * @dataProvider provideInvalidFeatureConfig
	 * @covers ::registerFeature
	 */
	public function testRegisterFeatureValidatesConfig( $expectedExceptionType, $config ) {
		$this->expectException( $expectedExceptionType );

		$featureManager = new FeatureManager();
		$featureManager->registerSet( 'set', true );
		$featureManager->registerFeature( 'feature', $config );
	}

	/**
	 * @covers ::isSetEnabled
	 */
	public function testIsSetEnabled() {
		$featureManager = new FeatureManager();
		$featureManager->registerSet( 'enabled', true );
		$featureManager->registerSet( 'disabled', false );

		$this->assertTrue( $featureManager->isSetEnabled( 'enabled' ) );
		$this->assertFalse( $featureManager->isSetEnabled( 'disabled' ) );
	}

	/**
	 * @covers ::isSetEnabled
	 */
	public function testIsSetEnabledThrowsExceptionWhenSetIsntRegistered() {
		$this->expectException( \InvalidArgumentException::class );

		$featureManager = new FeatureManager();
		$featureManager->isSetEnabled( 'foo' );
	}

	/**
	 * @covers ::registerFeature
	 */
	public function testRegisterFeatureThrowsExceptionWhenFeatureIsRegisteredTwice() {
		$this->expectException( \LogicException::class );

		$featureManager = new FeatureManager();
		$featureManager->registerFeature( 'featureA', [] );
		$featureManager->registerFeature( 'featureA', [] );
	}

	/**
	 * @covers ::isFeatureEnabled
	 */
	public function testIsFeatureEnabled() {
		$featureManager = new FeatureManager();
		$featureManager->registerSet( 'foo', false );
		$featureManager->registerFeature( 'requiresFoo', 'foo' );

		$this->assertFalse(
			$featureManager->isFeatureEnabled( 'requiresFoo' ),
			'A feature is disabled when the set that it requires is disabled.'
		);

		// ---

		$featureManager->registerSet( 'bar', true );
		$featureManager->registerSet( 'baz', true );

		$featureManager->registerFeature( 'requiresFooBar', [ 'foo', 'bar' ] );
		$featureManager->registerFeature( 'requiresBarBaz', [ 'bar', 'baz' ] );

		$this->assertFalse(
			$featureManager->isFeatureEnabled( 'requiresFooBar' ),
			'A feature is disabled when at least one set that it requires is disabled.'
		);

		$this->assertTrue( $featureManager->isFeatureEnabled( 'requiresBarBaz' ) );
	}

	/**
	 * @covers ::isFeatureEnabled
	 */
	public function testIsFeatureEnabledThrowsExceptionWhenFeatureIsntRegistered() {
		$this->expectException( \InvalidArgumentException::class );

		$featureManager = new FeatureManager();
		$featureManager->isFeatureEnabled( 'foo' );
	}
}
