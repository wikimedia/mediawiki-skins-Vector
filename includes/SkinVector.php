<?php
/**
 * Vector - Modern version of MonoBook with fresh look and many usability
 * improvements.
 *
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
 * @ingroup Skins
 */

use MediaWiki\MediaWikiServices;
use Vector\Constants;

/**
 * Skin subclass for Vector
 * @ingroup Skins
 * @final skins extending SkinVector are not supported
 * @unstable
 */
class SkinVector extends SkinTemplate {
	public $skinname = 'vector';
	public $stylename = 'Vector';
	public $template = 'VectorTemplate';

	private $responsiveMode = false;

	/**
	 * Enables the responsive mode
	 */
	public function enableResponsiveMode() {
		if ( !$this->responsiveMode ) {
			$out = $this->getOutput();
			$out->addMeta( 'viewport', 'width=device-width, initial-scale=1' );
			$out->addModuleStyles( 'skins.vector.styles.responsive' );
			$this->responsiveMode = true;
		}
	}

	/**
	 * Initializes output page and sets up skin-specific parameters
	 * @param OutputPage $out Object to initialize
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );

		if ( $this->getConfig()->get( 'VectorResponsive' ) ) {
			$this->enableResponsiveMode();
		}
	}

	/**
	 * Called by OutputPage::headElement when it is creating the
	 * `<body>` tag. Overrides method in Skin class.
	 * @param OutputPage $out
	 * @param array &$bodyAttrs
	 */
	public function addToBodyAttributes( $out, &$bodyAttrs ) {
		if ( $this->isLegacy() ) {
			$bodyAttrs['class'] .= ' skin-vector-legacy';
		}
	}

	/**
	 * @inheritDoc
	 * @return array
	 */
	public function getDefaultModules() {
		$modules = parent::getDefaultModules();
		// add vector skin styles and vector module
		$module = $this->isLegacy()
			? 'skins.vector.styles.legacy' : 'skins.vector.styles';
		$modules['styles']['skin'][] = $module;
		$modules['core'][] = 'skins.vector.js';

		return $modules;
	}

	/**
	 * Set up the VectorTemplate. Overrides the default behaviour of SkinTemplate allowing
	 * the safe calling of constructor with additional arguments. If dropping this method
	 * please ensure that VectorTemplate constructor arguments match those in SkinTemplate.
	 *
	 * @internal
	 * @param string $classname
	 * @return VectorTemplate
	 */
	protected function setupTemplate( $classname ) {
		$tp = new TemplateParser( __DIR__ . '/templates' );
		return new VectorTemplate( $this->getConfig(), $tp, $this->isLegacy() );
	}

	/**
	 * Whether the logo should be preloaded with an HTTP link header or not
	 * @since 1.29
	 * @return bool
	 */
	public function shouldPreloadLogo() {
		return true;
	}

	/**
	 * Whether or not the legacy version of the skin is being used.
	 *
	 * @return bool
	 */
	private function isLegacy() : bool {
		$isLatestSkinFeatureEnabled = MediaWikiServices::getInstance()
			->getService( Constants::SERVICE_FEATURE_MANAGER )
			->isFeatureEnabled( Constants::FEATURE_LATEST_SKIN );

		return !$isLatestSkinFeatureEnabled;
	}
}
