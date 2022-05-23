<?php

namespace MediaWiki\Skins\Vector\ResourceLoader;

use MediaWiki\Skins\Vector\Constants;
use ResourceLoaderContext;
use ResourceLoaderUserModule;

class VectorResourceLoaderUserModule extends ResourceLoaderUserModule {
	/**
	 * @inheritDoc
	 */
	protected function getPages( ResourceLoaderContext $context ) {
		$skin = $context->getSkin();
		$config = $this->getConfig();
		$user = $context->getUserObj();
		$pages = [];
		if ( $config->get( 'AllowUserCss' ) && !$user->isAnon() && ( $skin === Constants::SKIN_NAME_MODERN ) ) {
			$userPage = $user->getUserPage()->getPrefixedDBkey();
			$pages["$userPage/vector.js"] = [ 'type' => 'script' ];
		}
		return $pages;
	}
}
