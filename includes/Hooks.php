<?php

namespace MediaWiki\Skins\Vector;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use MediaWiki\ResourceLoader as RL;
use MediaWiki\Skin\Hook\SkinPageReadyConfigHook;
use MediaWiki\Skins\Vector\Hooks\HookRunner;
use MediaWiki\User\User;

/**
 * Presentation hook handlers for Vector skin.
 *
 * Hook handler method names should be in the form of:
 *	on<HookName>()
 * @package Vector
 * @internal
 */
class Hooks implements
	GetPreferencesHook,
	SkinPageReadyConfigHook
{
	/**
	 * Checks if the current skin is a variant of Vector
	 *
	 * @param string $skinName
	 * @return bool
	 */
	private static function isVectorSkin( string $skinName ): bool {
		return (
			$skinName === Constants::SKIN_NAME_LEGACY ||
			$skinName === Constants::SKIN_NAME_MODERN
		);
	}

	/**
	 * Generates config variables for skins.vector.search Resource Loader module (defined in
	 * skin.json).
	 *
	 * @param RL\Context $context
	 * @param Config $config
	 * @return array<string,mixed>
	 */
	public static function getVectorSearchResourceLoaderConfig(
		RL\Context $context,
		Config $config
	): array {
		$additionalSearchOptions = [
			'highlightQuery' =>
				VectorServices::getLanguageService()->canWordsBeSplitSafely( $context->getLanguage() )
		];

		$hookRunner = new HookRunner( MediaWikiServices::getInstance()->getHookContainer() );
		$hookRunner->onVectorSearchResourceLoaderConfig( $additionalSearchOptions );

		$vectorTypeahead = $config->get( 'VectorTypeahead' );
		$vectorTypeahead['options'] = array_merge( $vectorTypeahead['options'], $additionalSearchOptions );
		return $vectorTypeahead;
	}

	/**
	 * SkinPageReadyConfig hook handler
	 *
	 * Replace searchModule provided by skin.
	 *
	 * @since 1.35
	 * @param RL\Context $context
	 * @param mixed[] &$config Associative array of configurable options
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onSkinPageReadyConfig(
		RL\Context $context,
		array &$config
	) {
		// It's better to exit before any additional check
		if ( !self::isVectorSkin( $context->getSkin() ) ) {
			return;
		}
		// Tell the `mediawiki.page.ready` module not to wire up search.
		// This allows us to use the new Vue implementation.
		// Context has no knowledge of legacy / modern Vector
		// and from its point of view they are the same thing.
		// Please see the modules `skins.vector.js` and `skins.vector.legacy.js`
		// for the wire up of search.
		$config['searchModule'] = 'skins.vector.search';
		$config['watchLoadingStates'] = false;
	}

	/**
	 * Adds Vector specific user preferences that can only be accessed via API.
	 *
	 * @param User $user User whose preferences are being modified.
	 * @param array[] &$prefs Preferences description array, to be fed to a HTMLForm object.
	 */
	public function onGetPreferences( $user, &$prefs ): void {
		$vectorPrefs = [
			Constants::PREF_KEY_LIMITED_WIDTH => [
				'type' => 'toggle',
				'label-message' => 'vector-prefs-limited-width',
				'section' => 'rendering/skin/skin-prefs',
				'help-message' => 'vector-prefs-limited-width-help',
				'hide-if' => [ '!==', 'skin', Constants::SKIN_NAME_MODERN ],
			],
			Constants::PREF_KEY_FONT_SIZE => [
				'type' => 'select',
				'label-message' => 'vector-feature-custom-font-size-name',
				'section' => 'rendering/skin/skin-prefs',
				'options-messages' => [
					'vector-feature-custom-font-size-0-label' => '0',
					'vector-feature-custom-font-size-1-label' => '1',
					'vector-feature-custom-font-size-2-label' => '2',
				],
				'hide-if' => [ '!==', 'skin', Constants::SKIN_NAME_MODERN ],
			],
			Constants::PREF_KEY_PAGE_TOOLS_PINNED => [
				'type' => 'api'
			],
			Constants::PREF_KEY_MAIN_MENU_PINNED => [
				'type' => 'api'
			],
			Constants::PREF_KEY_TOC_PINNED => [
				'type' => 'api'
			],
			Constants::PREF_KEY_APPEARANCE_PINNED => [
				'type' => 'api'
			],
			Constants::PREF_KEY_NIGHT_MODE => [
				'type' => 'select',
				'label-message' => 'skin-theme-name',
				'help-message' => 'skin-theme-description',
				'section' => 'rendering/skin/skin-prefs',
				'options-messages' => [
					'skin-theme-day-label' => 'day',
					'skin-theme-night-label' => 'night',
					'skin-theme-os-label' => 'os',
				],
				'hide-if' => [ '!==', 'skin', Constants::SKIN_NAME_MODERN ],
			],
		];
		$prefs += $vectorPrefs;
	}
}
