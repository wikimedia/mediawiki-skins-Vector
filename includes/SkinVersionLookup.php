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

namespace Vector;

use Config;
use User;
use WebRequest;

/**
 * Given initial dependencies, retrieve the current skin version. This class does no parsing, just
 * the lookup.
 *
 * Skin version is evaluated in the following order:
 *
 * - useskinversion URL query parameter override. See readme.
 *
 * - User preference. The User object for new and existing accounts are updated by hook according to
 *   VectorDefaultSkinVersionForNewAccounts and VectorDefaultSkinVersionForExistingAccounts. See
 *   Hooks and skin.json.
 *
 *   If the skin version is evaluated prior to User preference hook invocations, an incorrect
 *   version may be returned as only query parameter and site configuration will be known.
 *
 * - Site configuration default. The default is controlled by VectorDefaultSkinVersion. This is used
 *   for anonymous users and as a fallback configuration. See skin.json.
 *
 * @unstable
 *
 * @package Vector
 * @internal
 */
final class SkinVersionLookup {
	/**
	 * @var WebRequest
	 */
	private $request;
	/**
	 * @var User
	 */
	private $user;
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * This constructor accepts all dependencies needed to obtain the skin version. The dependencies
	 * are lazily evaluated, not cached, meaning they always return the current results.
	 *
	 * @param WebRequest $request
	 * @param User $user
	 * @param Config $config
	 */
	public function __construct( WebRequest $request, User $user, Config $config ) {
		$this->request = $request;
		$this->user = $user;
		$this->config = $config;
	}

	/**
	 * Whether or not the legacy skin is being used.
	 *
	 * @return bool
	 * @throws \ConfigException
	 */
	public function isLegacy() {
		return $this->getVersion() === Constants::SKIN_VERSION_LEGACY;
	}

	/**
	 * The skin version as a string. E.g., `Constants::SKIN_VERSION_LEGACY`,
	 * `Constants::SKIN_VERSION_LATEST`, or maybe 'beta'. Note: it's likely someone will put arbitrary
	 * strings in the query parameter which means this function returns those strings as is.
	 *
	 * @return string
	 * @throws \ConfigException
	 */
	public function getVersion() {
		// Obtain the skin version from the `useskinversion` URL query parameter override, the user
		// preference, or the configured default.
		return (string)$this->request->getVal(
			Constants::QUERY_PARAM_SKIN_VERSION,
			$this->user->getOption(
				Constants::PREF_KEY_SKIN_VERSION,
				$this->config->get( Constants::CONFIG_KEY_DEFAULT_SKIN_VERSION )
			)
		);
	}
}
