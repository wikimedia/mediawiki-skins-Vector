<?php
namespace Vector;

use FatalError;

/**
 * A namespace for Vector constants for internal Vector usage only. **Do not rely on this file as an
 * API as it may change without warning at any time.**
 */
final class Constants {
	/**
	 * This is tightly coupled to the ConfigRegistry field in skin.json.
	 * @var string
	 */
	public const SKIN_NAME = 'vector';

	// These are tightly coupled to PREF_KEY_SKIN_VERSION and skin.json's configs. See skin.json for
	// documentation.
	/**
	 * @var string
	 */
	public const SKIN_VERSION_LEGACY = '1';
	/**
	 * @var string
	 */
	public const SKIN_VERSION_LATEST = '2';

	/**
	 * @var string
	 */
	public const SERVICE_CONFIG = 'Vector.Config';

	/**
	 * @var string
	 */
	public const SERVICE_FEATURE_MANAGER = 'Vector.FeatureManager';

	// These are tightly coupled to skin.json's config.
	/**
	 * @var string
	 */
	public const CONFIG_KEY_SHOW_SKIN_PREFERENCES = 'VectorShowSkinPreferences';
	/**
	 * @var string
	 */
	public const CONFIG_KEY_DEFAULT_SKIN_VERSION = 'VectorDefaultSkinVersion';
	/**
	 * @var string
	 */
	public const CONFIG_KEY_DEFAULT_SKIN_VERSION_FOR_EXISTING_ACCOUNTS =
		'VectorDefaultSkinVersionForExistingAccounts';
	/**
	 * @var string
	 */
	public const CONFIG_KEY_DEFAULT_SKIN_VERSION_FOR_NEW_ACCOUNTS =
		'VectorDefaultSkinVersionForNewAccounts';

	/**
	 * @var string
	 */
	public const PREF_KEY_SKIN_VERSION = 'VectorSkinVersion';

	// These are used in the Feature Management System.
	/**
	 * @var string
	 */
	public const CONFIG_KEY_FULLY_INITIALISED = 'FullyInitialised';

	/**
	 * @var string
	 */
	public const REQUIREMENT_FULLY_INITIALISED = 'FullyInitialised';

	/**
	 * This class is for namespacing constants only. Forbid construction.
	 * @throws FatalError
	 */
	private function __construct() {
		throw new FatalError( "Cannot construct a utility class." );
	}
}
