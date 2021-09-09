<?php
/**
 * @ingroup Skins
 * @package Vector
 * @internal
 */
class SkinVector22 extends SkinVector {
	/**
	 * @inheritDoc
	 */
	public function __construct( $options = [] ) {
		$options += [
			'template' => self::getTemplateOption(),
			'scripts' => self::getScriptsOption(),
			'styles' => self::getStylesOption(),
		];
		parent::__construct( $options );
	}

	/**
	 * Temporary static function while we deprecate SkinVector class.
	 *
	 * @return string
	 */
	public static function getTemplateOption() {
		return 'skin';
	}

	/**
	 * Temporary static function while we deprecate SkinVector class.
	 *
	 * @return array
	 */
	public static function getScriptsOption() {
		return [
			'skins.vector.js',
			'skins.vector.es6',
		];
	}

	/**
	 * Temporary static function while we deprecate SkinVector class.
	 *
	 * @return array
	 */
	public static function getStylesOption() {
		return [
			'mediawiki.ui.button',
			'skins.vector.styles',
			'skins.vector.icons',
			'mediawiki.ui.icon',
		];
	}
}
