module.exports = function( grunt ) {

	const sass = require( 'sass' );

	// Load all grunt tasks
	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );
	grunt.loadNpmTasks( '@lodder/grunt-postcss' );

	// Project configuration
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),

		// use legacy color notation until sass gets updated.
		// https://stackoverflow.com/questions/66825515/getting-error-in-css-with-rgb0-0-0-15
		// https://stylelint.io/user-guide/rules/list/color-function-notation/
		stylelint: {
			src: [ 'assets/css/src/**/*.scss' ],
			options: {
				customSyntax: 'postcss-scss',
				fix: true,
				configFile: '.stylelintrc.json'
			}
		},

		sass: {
			theme: {
				options: {
					implementation: sass,
					imagePath: 'assets/images',
					outputStyle: 'expanded',
					sourceMap: true
				},
				files: [ {
					expand: true,
					cwd: 'assets/css/src',
					src: [ '*.scss', '!_*.scss' ],
					dest: 'assets/css',
					ext: '.src.css'
				} ]
			}
		},

		/*
		 * Runs postcss plugins
		 */
		postcss: {

			/* Runs postcss + autoprefixer on the minified CSS. */
			theme: {
				options: {
					map: false,
					processors: [
						require( 'autoprefixer' )()
					]
				},
				files: [ {
					expand: true,
					cwd: 'assets/css',
					src: [ '*.src.css' ],
					dest: 'assets/css',
					ext: '.src.css'
				} ]
			}
		},

		cssmin: {
			theme: {
				files: [ {
					expand: true,
					cwd: 'assets/css',
					src: [ '*.src.css' ],
					dest: 'assets/css',
					ext: '.min.css'
				} ]
			}
		},

		watch: {
			css: {
				files: [ 'assets/css/src/**/*.scss' ],
				tasks: [ 'css' ],
				options: {
					debounceDelay: 500
				}
			}
		}

	} );

	// CSS Only
	grunt.registerTask( 'css', [ 'stylelint', 'sass', 'postcss', 'cssmin' ] );

	// Default task.
	grunt.registerTask( 'default', [ 'css' ] );
};
