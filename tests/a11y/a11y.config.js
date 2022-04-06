// @ts-nocheck
const config = {
	reportDir: 'docs/a11y',
	namespace: 'Vector',
	env: {
		development: {
			baseUrl: process.env.MW_SERVER,
			defaultPage: '/wiki/Polar_bear?useskin=vector-2022'
		},
		ci: {
			baseUrl: 'https://en.wikipedia.beta.wmflabs.org',
			defaultPage: '/wiki/Polar_bear'
		}
	},
	defaults: {
		viewport: {
			width: 1200,
			height: 1080
		},
		runners: [
			'axe',
			'htmlcs'
		],
		includeWarnings: true,
		includeNotices: true,
		hideElements: '#content'
	}
};

config.tests = ( envName ) => ( [
	{
		name: 'default',
		url: config.env[ envName ].baseUrl + config.env[ envName ].defaultPage
	},
	{
		name: 'logged_in',
		url: config.env[ envName ].baseUrl + config.env[ envName ].defaultPage,
		wait: '500',
		actions: [
			'click #p-personal-checkbox',
			'wait for .vector-user-menu-login a to be visible',
			'click .vector-user-menu-login a',
			'wait for #wpName1 to be visible',
			'set field #wpName1 to ' + process.env.MEDIAWIKI_USER,
			'set field #wpPassword1 to ' + process.env.MEDIAWIKI_PASSWORD,
			'click #wpLoginAttempt',
			'wait for #pt-userpage-2 to be visible' // Confirm login was successful
		]
	},
	{
		name: 'search',
		url: config.env[ envName ].baseUrl + config.env[ envName ].defaultPage,
		rootElement: '#p-search',
		wait: '500',
		actions: [
			'click #searchInput',
			'wait for .wvui-input__input to be added',
			'set field .wvui-input__input to Test'
		]
	}
] );

module.exports = config;
