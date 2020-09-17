/* eslint-env node */
var
	CleanWebpackPlugin = require( 'clean-webpack-plugin' ).CleanWebpackPlugin,
	MiniCssExtractPlugin = require( 'mini-css-extract-plugin' ),
	VueLoaderPlugin = require( 'vue-loader' ).VueLoaderPlugin,
	path = require( 'path' ),

	// The extension used for source map files. Per T173491, files with a .map extension cannot be
	// served from prod. It doesn't seem to be practical to rename the CSS source maps.
	jsSourceMapExtension = '.map.json';

/* eslint-disable jsdoc/valid-types */
/**
 * @param {Parameters<import('webpack').ConfigurationFactory>[0]} _env
 * @param {Parameters<import('webpack').ConfigurationFactory>[1]} argv
 * @return {ReturnType<import('webpack').ConfigurationFactory>}
 */
/* eslint-enable jsdoc/valid-types */
module.exports = function ( _env, argv ) {
	return {
		stats: {
			all: false,
			// Output a timestamp when a build completes. Useful when watching files.
			builtAt: true,
			errors: true,
			warnings: true
		},

		resolve: {
			alias: {
				// Share Vue.js dependencies. See https://github.com/webpack/webpack/issues/2134.
				vue: path.resolve( path.join( __dirname, 'node_modules', 'vue' ) )
			}
		},

		entry: { 'skins.vector.search': [ './resources/skins.vector.search' ] },

		// Accurate source maps come at the expense of build time. The source map is intentionally
		// exposed to users via sourceMapFilename for prod debugging. This goes against convention
		// as this source code is publicly distributed.
		devtool: argv.mode === 'production' ? 'source-map' : 'cheap-module-eval-source-map',

		output: {
			// Output to resources/dist.
			path: require( 'path' ).resolve( __dirname, 'resources/dist' ),

			sourceMapFilename: '[file]' + jsSourceMapExtension,

			// Set the name to avoid possible Webpack runtime collisions of globals with other
			// Webpack runtimes. See https://webpack.js.org/configuration/output/#outputuniquename.
			library: 'vector'
		},

		module: {
			rules: [
				{ test: /\.css$/, use: [ MiniCssExtractPlugin.loader, 'css-loader' ] },
				{ test: /\.vue$/, use: 'vue-loader' }
			]
		},

		plugins: [
			new CleanWebpackPlugin( {
				// Don't delete the ES5 linter config.
				cleanOnceBeforeBuildPatterns: [ '**/*', '!.eslintrc.json' ]
			} ),
			new MiniCssExtractPlugin(),
			new VueLoaderPlugin()
		]
	};
};
