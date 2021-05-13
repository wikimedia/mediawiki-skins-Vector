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
 * @since 1.37
 */

namespace Vector\FeatureManagement\Requirements;

use CentralIdLookup;
use Config;
use User;
use Vector\Constants;
use Vector\FeatureManagement\Requirement;
use WebRequest;

/**
 * Checks whether or not Language button in header should be used.
 *
 * @unstable
 *
 * @package Vector\FeatureManagement\Requirements
 * @internal
 */
final class LanguageInHeaderTreatmentRequirement implements Requirement {
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var WebRequest
	 */
	private $request;

	/**
	 * @var CentralIdLookup
	 */
	private $centralIdLookup;

	/**
	 * @param Config $config
	 * @param User $user
	 * @param WebRequest $request
	 * @param CentralIdLookup|null $centralIdLookup
	 */
	public function __construct(
		Config $config,
		User $user,
		WebRequest $request,
		?CentralIdLookup $centralIdLookup
	) {
		$this->config = $config;
		$this->user = $user;
		$this->request = $request;
		$this->centralIdLookup = $centralIdLookup;
	}

	/**
	 * @inheritDoc
	 */
	public function getName() : string {
		return Constants::REQUIREMENT_LANGUAGE_IN_HEADER;
	}

	/**
	 * If A/B test is enabled check whether the user is logged in and bucketed.
	 * Fallback to `VectorLanguageInHeader` config value.
	 *
	 * @inheritDoc
	 * @throws \ConfigException
	 */
	public function isMet() : bool {
		if ( $this->request->getCheck( Constants::QUERY_PARAM_LANGUAGE_IN_HEADER ) ) {
			return $this->request->getBool( Constants::QUERY_PARAM_LANGUAGE_IN_HEADER );
		}

		if (
			(bool)$this->config->get( Constants::CONFIG_LANGUAGE_IN_HEADER_TREATMENT_AB_TEST ) &&
			$this->user->isRegistered()
		) {
			$id = null;
			if ( $this->centralIdLookup ) {
				$id = $this->centralIdLookup->centralIdFromLocalUser( $this->user );
			}

			// $id will be 0 if the central ID lookup failed.
			if ( !$id ) {
				$id = $this->user->getId();
			}

			return $id % 2 === 0;
		}

		// If AB test is not enabled, fallback to checking config state.
		$languageInHeaderConfig = $this->config->get( Constants::CONFIG_KEY_LANGUAGE_IN_HEADER );

		// Backwards compatibility with config variables that have been set in production.
		if ( is_bool( $languageInHeaderConfig ) ) {
			$languageInHeaderConfig = [
				'logged_in' => $languageInHeaderConfig,
				'logged_out' => $languageInHeaderConfig,
			];
		} else {
			$languageInHeaderConfig = [
				'logged_in' => $languageInHeaderConfig['logged_in'] ?? false,
				'logged_out' => $languageInHeaderConfig['logged_out'] ?? false,
			];
		}

		return $languageInHeaderConfig[ $this->user->isRegistered() ? 'logged_in' : 'logged_out' ];
	}
}
