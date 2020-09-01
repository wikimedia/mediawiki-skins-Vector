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

use Vector\Constants;
use Vector\FeatureManagement\Requirements\SearchInHeaderRequirement;

/**
 * @group Vector
 * @coversDefaultClass \Vector\FeatureManagement\Requirements\SearchInHeaderRequirement
 */
class SearchInHeaderRequirementTest extends \MediaWikiTestCase {

	public function providerSearchInHeaderRequirement() {
		return [
			[
				// Is enabled for anons
				false,
				// is A-B test enabled
				false,
				// note 0 = anon user
				0,
				false,
				'If nothing enabled nobody gets search in header'
			],
			[
				// Is enabled for anons
				true,
				// is A-B test enabled
				false,
				// note 0 = anon user
				0,
				true,
				'All anons should get search in header if enable but when A/B test disabled'
			],
			[
				// Is enabled for anons
				true,
				// is A-B test enabled
				false,
				0,
				true,
				'All even logged in users should get search in header when A/B test disabled'
			],
			[
				// Is enabled for anons
				true,
				// is A-B test enabled
				false,
				1,
				true,
				'All odd logged in users should get search in header when A/B test disabled'
			],
			[
				// Is enabled for anons
				true,
				// is A-B test enabled
				true,
				// note 0 = anon user
				0,
				true,
				'All anons get search in header even when A/B enabled'
			],
			[
				// Is enabled for anons
				true,
				// is A-B test enabled
				true,
				2,
				true,
				'Bucketed users get search in header when A/B test enabled'
			],
			[
				// Is enabled for anons
				true,
				// is A-B test enabled
				true,
				1,
				false,
				'Non-Bucketed users do not get search in header when A/B test enabled'
			],
		];
	}

	/**
	 * @covers ::isMet
	 * @dataProvider providerSearchInHeaderRequirement
	 * @param bool $searchInHeaderConfigValue
	 * @param bool $abValue
	 * @param int $userId
	 * @param bool $expected
	 * @param string $msg
	 */
	public function testSearchInHeaderRequirement(
		$searchInHeaderConfigValue, $abValue, $userId, $expected, $msg
	) {
		$config = new HashConfig( [
			Constants::CONFIG_SEARCH_IN_HEADER => $searchInHeaderConfigValue,
			Constants::CONFIG_SEARCH_IN_HEADER_AB => $abValue,
		] );
		$user = $this->getTestUser()->getUser();
		$user->setId( $userId );

		$requirement = new SearchInHeaderRequirement(
			$config, $user
		);

		$this->assertSame( $requirement->isMet(), $expected, $msg );
	}
}
