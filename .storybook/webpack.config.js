const path = require( 'path' );

module.exports = {
	resolve: {
		alias: {
			// FIXME: These imports should be updated in the story files instead of here.
			'../resources/skins.vector.styles/Footer.less': '../resources/common/components/Footer.less',
			'../resources/skins.vector.styles/LanguageButton.less': '../resources/skins.vector.styles/components/LanguageButton.less',
			'../resources/skins.vector.styles/skin-legacy.less': '../resources/skins.vector.styles.legacy/skin-legacy.less',
			'../resources/skins.vector.styles/Logo.less': '../resources/skins.vector.styles/components/Logo.less',
			'../resources/skins.vector.styles/Menu.less': '../resources/common/components/Menu.less',
			'../.storybook/common.less': '../resources/common/common.less',
			'../resources/skins.vector.styles/MenuDropdown.less': '../resources/common/components/MenuDropdown.less',
			'../resources/skins.vector.styles/MenuPortal.less': '../resources/common/components/MenuPortal.less',
			'../resources/skins.vector.styles/MenuTabs.less': '../resources/common/components/MenuTabs.less',
			'../resources/skins.vector.styles/TabWatchstarLink.less': '../resources/common/components/TabWatchstarLink.less',
			'../resources/skins.vector.styles/SearchBox.less': '../resources/common/components/SearchBox.less',
			'../resources/skins.vector.styles/Sidebar.less': '../resources/skins.vector.styles/components/Sidebar.less',
			'../resources/skins.vector.styles/SidebarLogo.less': '../resources/common/components/SidebarLogo.less',
			'../resources/skins.vector.styles/MenuPortal.less': '../resources/common/components/MenuPortal.less'
		}
	},
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
					// FIXME: Disable resolving of CSS urls until Storybook is upgraded
					// to use Webpack 5 and array values for aliases
					// (which would cleanly resolve urls in LESS partial starting with `url(images/...)` )
					url: false
				}
			}, {
				loader: 'less-loader',
				options: {
					relativeUrls: true,
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
