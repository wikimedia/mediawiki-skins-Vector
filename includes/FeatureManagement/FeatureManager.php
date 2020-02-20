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

namespace Vector\FeatureManagement;

use Wikimedia\Assert\Assert;

/**
 * A simple feature manager.
 *
 * NOTE: This API hasn't settled. It may change at any time without warning. Please don't bind to
 * it unless you absolutely need to.
 *
 * @unstable
 *
 * @package FeatureManagement
 * @internal
 */
final class FeatureManager {

	/**
	 * A map of feature name to the array of requirements. A feature is only considered enabled when
	 * all of its requirements are met.
	 *
	 * See FeatureManager::registerFeature for additional detail.
	 *
	 * @var Array<string,string[]>
	 */
	private $features = [];

	/**
	 * A map of set name to whether the set is enabled.
	 *
	 * @var Array<string,bool>
	 */
	private $sets = [];

	/**
	 * Register a feature and its requirements.
	 *
	 * Essentially, a "feature" is a friendly (hopefully) name for some component, however big or
	 * small, that has some requirements. A feature manager allows us to decouple the component's
	 * logic from its requirements, allowing them to vary independently. Moreover, the use of
	 * friendly names wherever possible allows us to define common languages with our non-technical
	 * colleagues.
	 *
	 * ```php
	 * $featureManager->registerFeature( 'featureB', 'setA' );
	 * ```
	 *
	 * defines the "featureB" feature, which is enabled when the "setA" set is enabled.
	 *
	 * ```php
	 * $featureManager->registerFeature( 'featureC', [ 'setA', 'setB' ] );
	 * ```
	 *
	 * defines the "featureC" feature, which is enabled when the "setA" and "setB" sets are enabled.
	 * Note well that the feature is only enabled when _all_ requirements are met, i.e. the
	 * requirements are evaluated in order and logically `AND`ed together.
	 *
	 * @param string $feature The name of the feature
	 * @param string|array $requirements Which sets the feature requires to be enabled. As above,
	 *  you can define a feature that requires a single set via the shorthand
	 *
	 *  ```php
	 *  $featureManager->registerFeature( 'feature', 'set' );
	 *  // Equivalent to $featureManager->registerFeature( 'feature', [ 'set' ] );
	 *  ```
	 *
	 * @throws \LogicException If the feature is already registered
	 * @throws \Wikimedia\Assert\ParameterAssertionException If the feature's requirements aren't
	 *  the name of a single set or an array of sets
	 * @throws \InvalidArgumentException If the feature requires a set that isn't registered
	 */
	public function registerFeature( $feature, $requirements ) {
		//
		// Validation
		if ( array_key_exists( $feature, $this->features ) ) {
			throw new \LogicException( "Feature \"{$feature}\" is already registered." );
		}

		Assert::parameterType( 'string|array', $requirements, 'requirements' );

		$requirements = (array)$requirements;

		Assert::parameterElementType( 'string', $requirements, 'requirements' );

		foreach ( $requirements as $set ) {
			if ( !array_key_exists( $set, $this->sets ) ) {
				throw new \InvalidArgumentException(
					"Feature \"{$feature}\" references set \"{$set}\", which hasn't been registered"
				);
			}
		}

		// Mutation
		$this->features[$feature] = $requirements;
	}

	/**
	 * Gets whether the feature's requirements are met.
	 *
	 * @param string $feature
	 * @return bool
	 *
	 * @throws \InvalidArgumentException If the feature isn't registered
	 */
	public function isFeatureEnabled( $feature ) {
		if ( !array_key_exists( $feature, $this->features ) ) {
			throw new \InvalidArgumentException( "The feature \"{$feature}\" isn't registered." );
		}

		$requirements = $this->features[$feature];

		foreach ( $requirements as $set ) {
			if ( !$this->sets[$set] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Register a set.
	 *
	 * A set is some condition of the application state that a feature requires to be true or false.
	 *
	 * At the moment, these conditions can only be evaluated when the set is being defined, i.e. at
	 * boot time. At that time, certain objects mightn't have been fully loaded
	 * (see User::isSafeToLoad). See TODO.md for the proposed list of steps to allow this feature
	 * manager to handle that scenario.
	 *
	 * @param string $set The name of the set
	 * @param bool $isEnabled Whether the set is enabled
	 *
	 * @throws \LogicException If the set has already been registered
	 */
	public function registerSet( $set, $isEnabled ) {
		if ( array_key_exists( $set, $this->sets ) ) {
			throw new \LogicException( "Set \"{$set}\" is already registered." );
		}

		Assert::parameterType( 'boolean', $isEnabled, 'isEnabled' );

		$this->sets[$set] = $isEnabled;
	}

	/**
	 * Gets whether the set is enabled.
	 *
	 * @param string $set The name of the set
	 * @return bool
	 *
	 * @throws \InvalidArgumentException If the set isn't registerd
	 */
	public function isSetEnabled( $set ) {
		if ( !array_key_exists( $set, $this->sets ) ) {
			throw new \InvalidArgumentException( "Set \"{$set}\" isn't registered." );
		}

		return $this->sets[$set];
	}
}
