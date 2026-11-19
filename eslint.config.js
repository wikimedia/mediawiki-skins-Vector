'use strict';

const { defineConfig, globalIgnores } = require( 'eslint/config' );
const wikimedia = require( 'eslint-config-wikimedia/flat' );
const noJquery = require( 'eslint-plugin-no-jquery' );

// Directories with their own config, which other configs don't apply to.
// These were directories with `"root": true` in .eslintrc.json.
const rootDirs = {
	resources: [ 'resources/**' ],
	tests: [ 'tests/**' ]
};

// Shared by resources and tests (was resources/.eslintrc.json)
const resourcesConfig = [
	noJquery.configs[ 'flat/all' ],
	wikimedia( 'client', 'jquery', 'mediawiki' ),
	{
		languageOptions: {
			sourceType: 'module',
			globals: {
				exports: 'writable'
			}
		},
		rules: {
			'no-jquery/no-other-utils': 'error',
			'no-jquery/no-trigger': 'error',
			'no-jquery/no-jquery-constructor': 'error',
			'no-jquery/no-find-collection': 'error',
			'no-jquery/no-attr': 'error',
			'no-jquery/no-class': 'error',
			'no-jquery/no-text': 'error',
			'no-jquery/no-css': 'error',
			'no-jquery/no-other-methods': 'error',
			'no-jquery/no-html': 'error',
			'no-jquery/no-class-state': 'error',
			'no-jquery/no-deferred': 'error',
			'no-jquery/no-when': 'error',
			'no-jquery/no-extend': 'error',
			'mediawiki/no-nodelist-unsupported-methods': 'off',
			'jsdoc/no-undefined-types': 'off',
			'compat/compat': 'off',
			'mediawiki/class-doc': 'off',
			'vue/dot-location': 'off'
		}
	}
];

module.exports = defineConfig( [
	globalIgnores( [
		'docs/',
		'a11y/',
		'i18n/',
		'vendor/',
		'coverage/',
		'tests/phpunit/unit/Components/__snapshots__/'
	] ),

	// Gruntfile.js, etc.
	{
		ignores: Object.values( rootDirs ).flat(),
		extends: [ wikimedia( 'server' ) ]
	},

	// Code which runs in MediaWiki
	{
		files: rootDirs.resources,
		extends: resourcesConfig
	},

	// Jest tests
	{
		files: rootDirs.tests,
		extends: [ resourcesConfig, wikimedia( 'jest' ) ],
		languageOptions: {
			ecmaVersion: 2018,
			sourceType: 'module'
		},
		rules: {
			'es-x/no-rest-spread-properties': 'off',
			'es-x/no-async-functions': 'off'
		}
	}
] );
