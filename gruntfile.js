module.exports = function(grunt) {

    // 1. All configuration goes here 
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            dist: {
                src: [
                    'cms/wp-content/themes/timby/js/libs/*.js', // All JS in the libs folder
                    'cms/wp-content/themes/timby/js/*.js'  // This specific file
                ],
                dest: 'cms/wp-content/themes/timby/js/build/production.js',
            }
        },

        uglify: {
            build: {
                src:  'cms/wp-content/themes/timby/js/build/production.js',
                dest: 'cms/wp-content/themes/timby/js/build/production.min.js'
            }
        },

        imagemin: {
            dynamic: {
                files: [{
                    expand: true,
                    cwd: 'cms/wp-content/themes/timby/images/',
                    src: ['*.{png,jpg,gif}'],
                    dest: 'cms/wp-content/themes/timby/images/build/'
                }]
            }
        },

        sass: {
            dist: {
                options: {
                    style: 'compressed'
                },
                files: {
                    'cms/wp-content/themes/timby/css/global.css': 'cms/wp-content/themes/timby/sass/global.sass'
                }
            } 
        },

        watch: {
            scripts: {
                files: ['js/*.js'],
                tasks: ['concat', 'uglify'],
                options: {
                    spawn: false,
                }
            },
            css: {
                files: ['cms/wp-content/themes/timby/sass/global.sass', 'cms/wp-content/themes/timby/sass/modules/*'],
                tasks: ['sass'],
                options: {
                    spawn: false,
                }
            }
        }

    });

    // 3. Where we tell Grunt we plan to use plug-ins.
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');

    // 4. Where we tell Grunt what to do when we type "grunt" into the terminal.
    grunt.registerTask('default', ['concat','uglify','imagemin','sass','watch']);

};