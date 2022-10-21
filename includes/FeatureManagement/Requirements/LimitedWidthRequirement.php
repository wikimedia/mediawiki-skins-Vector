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
 */

namespace MediaWiki\Skins\Vector\FeatureManagement\Requirements;

use MediaWiki\Skins\Vector\Constants;
use MediaWiki\Skins\Vector\FeatureManagement\Requirement;
use MediaWiki\User\UserOptionsLookup;
use Title;
use User;

/**
 * The `MaxWidthRequirement` for skin
 * @package MediaWiki\Skins\Vector\FeatureManagement\Requirements
 */
final class LimitedWidthRequirement implements Requirement {
	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var UserOptionsLookup
	 */
	 private $userOptionsLookup;

	/**
	 * This constructor accepts all dependencies needed to determine whether
	 * the overridable config is enabled for the current user and request.
	 *
	 * @param User $user
	 * @param UserOptionsLookup $userOptionsLookup
	 * @param Title|null $title
	 */
	public function __construct(
		User $user,
		UserOptionsLookup $userOptionsLookup,
		$title = null
	) {
		$this->user = $user;
		$this->userOptionsLookup = $userOptionsLookup;
		$this->title = $title;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return Constants::REQUIREMENT_LIMITED_WIDTH;
	}

	/**
	 * Indicates if this skin should be shown with max-width.
	 * @internal
	 *
	 * @return bool
	 */
	public function hasUserLimitedWidthEnabled() {
		$user = $this->user;
		$userOptionsLookup = $this->userOptionsLookup;
		$isLimitedWidth = $userOptionsLookup->getOption(
			$user,
			Constants::PREF_KEY_LIMITED_WIDTH
		);
		$isLimitedWidth = $isLimitedWidth === null ? true : $userOptionsLookup->getBoolOption(
			$user,
			Constants::PREF_KEY_LIMITED_WIDTH
		);
		return $this->title && $isLimitedWidth;
	}

	/**
	 * Check query parameter to override config or not.
	 * Then check for AB test value.
	 * Fallback to config value.
	 *
	 * @inheritDoc
	 */
	public function isMet(): bool {
		return $this->hasUserLimitedWidthEnabled();
	}
}
