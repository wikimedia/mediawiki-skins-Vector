<?php

class VectorHooks {
	/**
	 * Register the 'skins.vector.styles' hook. This is temporary until responsive
	 * mode becomes the default.
	 */
	public static function onResourceLoaderRegisterModules( ResourceLoader $rl ) {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'vector' );
		$definition = array(
			'position' => 'top',
			'styles' => array(
				'screen.less' => array(
					'media' => 'screen',
				),
				'screen-hd.less' => array(
					'media' => 'screen and (min-width: 982px)',
				),
			),
			'localBasePath' => __DIR__,
			'remoteSkinPath' => 'Vector'
		);
		if ( $config->get( 'VectorResponsive' ) ) {
			$definition['styles']['responsive.less'] = array( 'media' => 'screen and (max-width: 768px)' );
		}

		$rl->register( 'skins.vector.styles', $definition );
	}
}
