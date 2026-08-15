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
 * @since 1.42
 */
namespace MediaWiki\Skins\Vector\Tests\Unit\Components;

use MediaWikiUnitTestCase;

class VectorComponentSnapshotTestCase extends MediaWikiUnitTestCase {

	public function updateSnapshot( $snapshotName, $data ) {
		$snapshotPath = __DIR__ . '/__snapshots__/' . $snapshotName;
		file_put_contents( $snapshotPath, json_encode( $data, JSON_PRETTY_PRINT ) . "\n" );
	}

	public function assertEqualsSnapshot( $snapshotName, $data, $msg = '' ) {
		$snapshotPath = __DIR__ . '/__snapshots__/' . $snapshotName;

		// Update snapshot if --update-snapshots flag is set via environment variable
		if ( getenv( 'PHPUNIT_UPDATE_SNAPSHOTS' ) ) {
			$this->updateSnapshot( $snapshotName, $data );
		}

		$actualData = file_exists( $snapshotPath ) ?
			json_decode( file_get_contents( $snapshotPath ), true ) : [];

		$this->assertEquals(
			$actualData,
			$data,
			$msg . ' If changes are expected, update snapshot by running: '
				. '`PHPUNIT_UPDATE_SNAPSHOTS=1 composer phpunit:unit`'
		);
	}
}
