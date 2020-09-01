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
	 * This constructor accepts all dependencies needed to determine
	 * whether search in header is enabled for current user and config.
	 *
	 * @param \Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * @inheritDoc
	 */
	public function getName() : string {
		return Constants::REQUIREMENT_SEARCH_IN_HEADER;
	}

	/**
	 * @inheritDoc
	 * @throws \ConfigException
	 */
	public function isMet() : bool {
		return (bool)$this->config->get( Constants::CONFIG_SEARCH_IN_HEADER );
	}
}
