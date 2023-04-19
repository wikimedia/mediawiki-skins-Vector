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

namespace MediaWiki\Skins\Vector\FeatureManagement\Requirements;

use CentralIdLookup;
use Config;
use MediaWiki\Skins\Vector\FeatureManagement\Requirement;
use User;

/**
 * @package MediaWiki\Skins\Vector\FeatureManagement\Requirements
 * @internal
 */
class ABRequirement implements Requirement {
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var CentralIdLookup
	 */
	private $centralIdLookup;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var string The name of the requirement
	 */
	private $name;

	/**
	 * @var string The name of the experiment
	 */
	private $experimentName;

	/**
	 * @param Config $config
	 * @param User $user
	 * @param CentralIdLookup|null $centralIdLookup
	 * @param string $name The name of the requirement
	 * @param string $experimentName The name of the experiment
	 */
	public function __construct(
		Config $config,
		User $user,
		?CentralIdLookup $centralIdLookup,
		string $name,
		string $experimentName
	) {
		$this->config = $config;
		$this->user = $user;
		$this->centralIdLookup = $centralIdLookup;
		$this->name = $name;
		$this->experimentName = $experimentName;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Returns true if the user is logged-in and false otherwise.
	 *
	 * @inheritDoc
	 */
	public function isMet(): bool {
		// Get the experiment configuration from the config object.
		$experiment = $this->config->get( 'VectorWebABTestEnrollment' );

		$id = null;
		if ( $this->centralIdLookup ) {
			$id = $this->centralIdLookup->centralIdFromLocalUser( $this->user );
		}

		// $id will be 0 if the central ID lookup failed.
		if ( !$id ) {
			$id = $this->user->getId();
		}

		// Check if the experiment is not enabled or does not match the specified name.
		if ( !$experiment['enabled'] || $experiment['name'] !== $this->experimentName ) {
			// If the experiment is not enabled or does not match the specified name,
			// return true, indicating that the metric is "met"
			return true;
		} else {
			// If the experiment is enabled and matches the specified name,
			// calculate the user's variant based on their central ID
			$variant = $id % 2;

			// Cast the variant value to a boolean and return it, indicating whether
			// the user is in the "control" or "test" group.
			return (bool)$variant;
		}
	}
}
