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

namespace Vector\FeatureManagement\Requirements;

use MediaWiki\User\UserOptionsLookup;
use User;
use Vector\Constants;
use Vector\FeatureManagement\Requirement;
use WebRequest;

/**
 * Checks if the current skin is modern Vector.
 *
 * @unstable
 *
 * @package Vector\FeatureManagement\Requirements
 * @internal
 */
final class LatestSkinVersionRequirement implements Requirement {

	/**
	 * @var WebRequest
	 */
	private $request;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var UserOptionsLookup
	 */
	private $userOptionsLookup;

	/**
	 * This constructor accepts all dependencies needed to obtain the skin version.
	 *
	 * @param WebRequest $request
	 * @param User $user
	 * @param UserOptionsLookup $userOptionsLookup
	 */
	public function __construct( WebRequest $request, User $user, UserOptionsLookup $userOptionsLookup ) {
		$this->request = $request;
		$this->user = $user;
		$this->userOptionsLookup = $userOptionsLookup;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return Constants::REQUIREMENT_LATEST_SKIN_VERSION;
	}

	/**
	 * @inheritDoc
	 * @throws \ConfigException
	 */
	public function isMet(): bool {
		$useSkin = $this->request->getVal( 'useskin' );
		$user = $this->user;
		if ( !$useSkin && $user->isSafeToLoad() ) {
			$useSkin = $this->userOptionsLookup->getOption(
				$user,
				Constants::PREF_KEY_SKIN
			);
		}
		return $useSkin === Constants::SKIN_NAME_MODERN;
	}
}
