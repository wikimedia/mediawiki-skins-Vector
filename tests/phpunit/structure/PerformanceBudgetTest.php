<?php

namespace MediaWiki\Skins\Vector\Tests\Structure;

use DerivativeContext;
use HashBagOStuff;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Request\FauxRequest;
use MediaWiki\ResourceLoader\Context;
use MediaWiki\ResourceLoader\Module;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use RequestContext;
use Wikimedia\DependencyStore\KeyValueDependencyStore;

/**
 * @group Database
 */
class PerformanceBudgetTest extends MediaWikiIntegrationTestCase {

	/**
	 * Get the maximum size of modules in bytes as defined in bundlesize.config.json
	 *
	 * @param string $skinName
	 *
	 * @return array
	 */
	protected function getMaxSize( $skinName ) {
		$configFile = dirname( __DIR__, 3 ) . '/bundlesize.config.json';
		$bundleSizeConfig = json_decode( file_get_contents( $configFile ), true );
		return $bundleSizeConfig[ 'total' ][ $skinName ] ?? [];
	}

	/**
	 * Calculates the size of a module
	 *
	 * @param string $moduleName
	 * @param string $skinName
	 *
	 * @return float|int
	 * @throws \Wikimedia\RequestTimeout\TimeoutException
	 * @throws MediaWiki\Config\ConfigException
	 */
	protected function getContentTransferSize( $moduleName, $skinName ) {
		// Calculate Size
		$resourceLoader = MediaWikiServices::getInstance()->getResourceLoader();
		$resourceLoader->setDependencyStore( new KeyValueDependencyStore( new HashBagOStuff() ) );
		$request = new FauxRequest(
			[
				'lang' => 'en',
				'modules' => $moduleName,
				'skin' => $skinName,
			]
		);

		$context = new Context( $resourceLoader, $request );
		$module = $resourceLoader->getModule( $moduleName );
		$contentContext = new \MediaWiki\ResourceLoader\DerivativeContext( $context );
		$contentContext->setOnly(
			$module->getType() === Module::LOAD_STYLES
				? Module::TYPE_STYLES
				: Module::TYPE_COMBINED
		);
		// Create a module response for the given module and calculate the size
		$content = $resourceLoader->makeModuleResponse( $contentContext, [ $moduleName => $module ] );
		$contentTransferSize = strlen( gzencode( $content, 9 ) );
		// Adjustments for core modules [T343407]
		$contentTransferSize -= 17;
		return $contentTransferSize;
	}

	/**
	 * Prepares a skin for testing, assigning context and output page
	 *
	 * @param string $skinName
	 *
	 * @return \Skin
	 * @throws \SkinException
	 */
	protected function prepareSkin( string $skinName ): \Skin {
		$skinFactory = MediaWikiServices::getInstance()->getSkinFactory();
		$skin = $skinFactory->makeSkin( $skinName );
		$title = Title::newFromText( 'Hello' );
		$context = new DerivativeContext( RequestContext::getMain() );
		$context->setTitle( $title );
		$context->setSkin( $skin );
		$outputPage = new OutputPage( $context );
		$context->setOutput( $outputPage );
		$skin->setContext( $context );
		$outputPage->setTitle( $title );
		$outputPage->output( true );
		return $skin;
	}

	/**
	 * Converts a string to bytes
	 *
	 * @param string|int|float $size
	 *
	 * @return float|int
	 */
	private function getSizeInBytes( $size ) {
		if ( is_string( $size ) ) {
			if ( strpos( $size, 'KB' ) !== false || strpos( $size, 'kB' ) !== false ) {
				$size = (float)str_replace( [ 'KB', 'kB', ' KB', ' kB' ], '', $size );
				$size = $size * 1024;
			} elseif ( strpos( $size, 'B' ) !== false ) {
				$size = (float)str_replace( [ ' B', 'B' ], '', $size );
			}
		}
		return $size;
	}

	/**
	 * Get the list of skins and their maximum size
	 *
	 * @return array
	 */
	public function provideSkinsForModulesSize() {
		$allowedSkins = [ 'vector-2022', 'vector' ];
		$skins = [];
		foreach ( $allowedSkins as $skinName ) {
			$maxSizes = $this->getMaxSize( $skinName );
			if ( empty( $maxSizes ) ) {
				continue;
			}
			$skins[ $skinName ] = [ $skinName, $maxSizes ];
		}
		return $skins;
	}

	/**
	 * Tests the size of modules in allowed skins
	 *
	 * @param string $skinName
	 * @param array $maxSizes
	 *
	 * @dataProvider provideSkinsForModulesSize
	 * @coversNothing
	 *
	 * @return void
	 * @throws \Wikimedia\RequestTimeout\TimeoutException
	 * @throws MediaWiki\Config\ConfigException
	 */
	public function testTotalModulesSize( $skinName, $maxSizes ) {
		$skin = $this->prepareSkin( $skinName );
		$moduleStyles = $skin->getOutput()->getModuleStyles();
		$size = 0;
		foreach ( $moduleStyles as $moduleName ) {
			$size += $this->getContentTransferSize( $moduleName, $skinName );
		}
		$stylesMaxSize = $this->getSizeInBytes( $maxSizes[ 'styles' ] );
		$message = "Performance budget for style in skin $skinName on main article namespace has been exceeded." .
			" Total size of style modules is $size bytes is greater than budget size $stylesMaxSize bytes" .
			" Reduce styles loaded on page load or talk to skin maintainer before modifying the budget.";
		$this->assertLessThanOrEqual( $stylesMaxSize, $size, $message );
		$modulesScripts = $skin->getOutput()->getModules();
		$size = 0;
		foreach ( $modulesScripts as $moduleName ) {
			$size += $this->getContentTransferSize( $moduleName, $skinName );
		}
		$scriptsMaxSize = $this->getSizeInBytes( $maxSizes[ 'scripts' ] );
		$message = "Performance budget for scripts in skin $skinName on main article namespace has been exceeded." .
			" Total size of script modules is $size bytes is greater than budget size $scriptsMaxSize bytes" .
			" Reduce scripts loaded on page load or talk to skin maintainer before modifying the budget.";
		$this->assertLessThanOrEqual( $scriptsMaxSize, $size, $message );
	}
}
