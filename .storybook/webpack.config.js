const path = require( 'path' );

module.exports = {
	module: {
		rules: [ {
			test: /\.js$/,
			exclude: /node_modules/,
			use: {
				loader: 'babel-loader',
				options: {
					// Beware of https://github.com/babel/babel-loader/issues/690. Changes to browsers require
					// manual invalidation.
					cacheDirectory: true
				}
			}
		},
		{
			test: /\.css$/,
			use: [ {
				loader: 'style-loader'
			}, {
				loader: 'css-loader'
			} ]
		},
		{
			test: /\.(gif|png|jpe?g|svg)$/i,
			loader: 'file-loader',
			options: {
				paths: [
					path.resolve( __dirname, './resolve-imports' )
				]
			}
		},
		{
			// in core some LESS imports don't specify filename
			test: /\.less$/,
			use: [ {
				loader: 'style-loader'
			}, {
				loader: 'css-loader',
				options: {
					// Disable image resolution. This fails on remote URLs.
					url: false,
					// Use CommonJS modules for styles. This doesn't seem to work with the current
					// version of Storybook. Reevaluate when upgrading.
					esModule: false
				}
			}, {
				loader: 'less-loader',
				options: {
					lessOptions: {
						relativeUrls: false,
						strictUnits: true,
						paths: [
							path.resolve( __dirname, 'resolve-imports' )
						]
					}
				}
			} ]
		},
	]
	}
};
