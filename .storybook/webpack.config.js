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
				loader: 'css-loader'
			}, {
				loader: 'less-loader',
				options: {
					relativeUrls: false,
					strictUnits: true,
					paths: [
					 	path.resolve( __dirname, 'resolve-imports' )
					]
				}
			} ]
		},
	]
	}
};
