var grunt = require('grunt');

require("load-grunt-tasks")(grunt); // npm install --save-dev load-grunt-tasks

grunt.initConfig({
	babel : {
		compile : {
			options : {
				sourceMap : false
			},
			files : [ {
				expand : true,
				cwd : 'jsx/', // Custom folder
				src : [ '**/*.jsx' ],
				dest : 'js/react-components/', // Custom folder
				ext : '.js'
			} ]
		},
	},
	uglify : {
		options : {
			mangle : true,
		},
		files : {
			expand : true,
			cwd : 'js/react-components',
			src : '**/*.js',
			dest : 'js/components'
		}
	},
	clean : {
		folder : [ 'js/react-components/' ]
	},
	watch : {
		options : {
		},
		react : {
			files : [ 'jsx/**/*.jsx', 'jsx/*.jsx' ],
			tasks : [ 'babel', 'uglify', 'clean' ]
		}
	}
});

grunt.loadNpmTasks('grunt-contrib-uglify');
grunt.loadNpmTasks('grunt-contrib-clean');
grunt.loadNpmTasks('grunt-contrib-watch');

grunt.registerTask("default", [ "babel", "uglify", "clean" ]);