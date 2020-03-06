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
	 * A map of requirement name to whether the requirement is met.
	 *
	 * @var Array<string,bool>
	 */
	private $requirements = [];

	/**
	 * Register a feature and its requirements.
	 *
	 * Essentially, a "feature" is a friendly (hopefully) name for some component, however big or
	 * small, that has some requirements. A feature manager allows us to decouple the component's
	 * logic from its requirements, allowing them to vary independently. Moreover, the use of
	 * friendly names wherever possible allows us to define a common language with our non-technical
	 * colleagues.
	 *
	 * ```php
	 * $featureManager->registerFeature( 'featureA', 'requirementA' );
	 * ```
	 *
	 * defines the "featureA" feature, which is enabled when the "requirementA" requirement is met.
	 *
	 * ```php
	 * $featureManager->registerFeature( 'featureB', [ 'requirementA', 'requirementB' ] );
	 * ```
	 *
	 * defines the "featureB" feature, which is enabled when the "requirementA" and "requirementB"
	 * requirements are met. Note well that the feature is only enabled when _all_ requirements are
	 * met, i.e. the requirements are evaluated in order and logically `AND`ed together.
	 *
	 * @param string $feature The name of the feature
	 * @param string|array $requirements The feature's requirements. As above, you can define a
	 * feature that requires a single requirement via the shorthand
	 *
	 *  ```php
	 *  $featureManager->registerFeature( 'feature', 'requirementA' );
	 *  // Equivalent to $featureManager->registerFeature( 'feature', [ 'requirementA' ] );
	 *  ```
	 *
	 * @throws \LogicException If the feature is already registered
	 * @throws \Wikimedia\Assert\ParameterAssertionException If the feature's requirements aren't
	 *  the name of a single requirement or a list of requirements
	 * @throws \InvalidArgumentException If the feature references a requirement that isn't
	 *  registered
	 */
	public function registerFeature( $feature, $requirements ) {
		//
		// Validation
		if ( array_key_exists( $feature, $this->features ) ) {
			throw new \LogicException( sprintf(
				'Feature "%s" is already registered.',
				$feature
			) );
		}

		Assert::parameterType( 'string|array', $requirements, 'requirements' );

		$requirements = (array)$requirements;

		Assert::parameterElementType( 'string', $requirements, 'requirements' );

		foreach ( $requirements as $name ) {
			if ( !array_key_exists( $name, $this->requirements ) ) {
				throw new \InvalidArgumentException( sprintf(
					'Feature "%s" references requirement "%s", which hasn\'t been registered',
					$feature,
					$name
				) );
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

		foreach ( $requirements as $name ) {
			if ( !$this->requirements[$name] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Register a requirement.
	 *
	 * A requirement is some condition of the application state that a feature requires to be true
	 * or false.
	 *
	 * At the moment, these conditions can only be evaluated when the requirement is being defined,
	 * i.e. at boot time. At that time, certain objects mightn't have been fully loaded (see
	 * User::isSafeToLoad). See TODO.md for the proposed list of steps to allow this feature
	 * manager to handle that scenario.
	 *
	 * @param string $name The name of the requirement
	 * @param bool $isMet Whether the requirement is met
	 *
	 * @throws \LogicException If the requirement has already been registered
	 */
	public function registerRequirement( $name, $isMet ) {
		if ( array_key_exists( $name, $this->requirements ) ) {
			throw new \LogicException( "The requirement \"{$name}\" is already registered." );
		}

		Assert::parameterType( 'boolean', $isMet, 'isMet' );

		$this->requirements[$name] = $isMet;
	}

	/**
	 * Gets whether the requirement is met.
	 *
	 * @param string $name The name of the requirement
	 * @return bool
	 *
	 * @throws \InvalidArgumentException If the requirement isn't registered
	 */
	public function isRequirementMet( $name ) {
		if ( !array_key_exists( $name, $this->requirements ) ) {
			throw new \InvalidArgumentException( "Requirement \"{$name}\" isn't registered." );
		}

		return $this->requirements[$name];
	}
}
