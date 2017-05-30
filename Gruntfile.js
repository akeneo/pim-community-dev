'use strict';

module.exports = function (grunt) {
    require('time-grunt')(grunt);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        eslint: {
            options: {
                quiet: true,
                configFile: '.eslintrc'
            },
            target: [
                'Gruntfile.js',
                'src/**/*.js',
                '!src/**/lib/**/*.js',
                '!src/**/DataGridBundle/**/*.js',
                '!src/**/NavigationBundle/**/*.js',
                '!src/Oro/**/**/*.js'
            ]
        },
        recess: {
            all: [
                'web/bundles/pim*/**/*.css',
                'web/bundles/pim*/**/*.less',
                '!web/bundles/**/lib/**/*.css',
                '!web/bundles/**/lib/**/*.less'
            ],
            options: {
                strictPropertyOrder: false,
                noIDs: false,
                noOverqualifying: false,
                noUniversalSelectors: false
            }
        },
        jasmine: {
            specs: {
                src: '/bundles/ororequirejs/lib/require.js',
                options: {
                    specs: 'src/**/spec/**/*Spec.js',
                    vendor: ['.grunt/grunt-contrib-jasmine/es5-shim.js'],
                    template: require('grunt-template-jasmine-requirejs'),
                    templateOptions: {
                        requireConfigFile: 'web/js/require-config.js',
                        requireConfig: {
                            baseUrl: 'web/bundles',
                            shim: {
                                'oro/routes': {
                                    deps: ['routing'],
                                    init: function (routing) {
                                        return routing;
                                    }
                                }
                            },
                            map: {
                                '*': {
                                    'routing': 'oro/routes'
                                },
                                'oro/routes': {
                                    'routing': 'routing'
                                }
                            },
                            paths: {
                                'oro/routes': '../js/routes'
                            }
                        }
                    }
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-jasmine');
    grunt.loadNpmTasks('grunt-recess');
    grunt.loadNpmTasks('grunt-eslint');

    grunt.registerTask('test', ['jasmine']);
    grunt.registerTask('codestyle', ['eslint', 'recess']);
    grunt.registerTask('default', ['codestyle', 'test']);
};
