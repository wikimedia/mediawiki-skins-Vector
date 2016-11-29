/* eslint-env node */
module.exports = function ( grunt ) {
	var conf = grunt.file.readJSON( 'skin.json' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			all: [
				'*.js',
				'**/*.js',
				'!node_modules/**'
			]
		},
		jsonlint: {
			all: [
				'*.json',
				'**/*.json',
				'!node_modules/**'
			]
		},
		banana: conf.MessagesDirs,
		stylelint: {
			all: [
				'*.{le,c}ss',
				'skinStyles/*.{le,c}ss'
			]
		}
	} );

	grunt.registerTask( 'test', [ 'eslint', 'jsonlint', 'banana', 'stylelint' ] );
	grunt.registerTask( 'default', 'test' );
};
