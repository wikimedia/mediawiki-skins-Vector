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
 * @since 1.36
 */

namespace Vector\FeatureManagement\Requirements;

use Config;
use Vector\Constants;
use Vector\FeatureManagement\Requirement;

/**
 * Check whether the search should be part of the header or part of
 * the tabs (as in the old design).
 * The search in header is enabled if:
 *  - the associated feature flag has been enabled
 *  - the feature flag for the A/B test is enabled,
 *     and the user is logged and bucketed,
 *     in which case 50% of logged in users will see the search in the header
 *
 * @unstable
 *
 * @package Vector\FeatureManagement\Requirements
 * @internal
 */
final class SearchInHeaderRequirement implements Requirement {
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var \User
	 */
	private $user;

	/**
	 * This constructor accepts all dependencies needed to determine
	 * whether search in header is enabled for current user and config.
	 *
	 * @param \Config $config
	 * @param \User $user
	 */
	public function __construct( \Config $config, \User $user ) {
		$this->config = $config;
		$this->user = $user;
	}

	/**
	 * @inheritDoc
	 */
	public function getName() : string {
		return Constants::REQUIREMENT_SEARCH_IN_HEADER;
	}

	/**
	 * If A/B test is enabled check whether the user is logged in and bucketed
	 * @return bool
	 */
	private function isBucketed() {
		$isABTestEnabled = (bool)$this->config->get( Constants::CONFIG_SEARCH_IN_HEADER_AB );

		if ( $isABTestEnabled ) {
			return $this->user->getId() % 2 === 0;
		} else {
			// if A/B test is disabled then resort to using CONFIG_SEARCH_IN_HEADER
			return (bool)$this->config->get( Constants::CONFIG_SEARCH_IN_HEADER );
		}
	}

	/**
	 * @inheritDoc
	 * @throws \ConfigException
	 */
	public function isMet() : bool {
		return $this->user->isRegistered() ?
			$this->isBucketed() : (bool)$this->config->get( Constants::CONFIG_SEARCH_IN_HEADER );
	}
}
