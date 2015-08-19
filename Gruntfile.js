/* jshint node:true */
'use strict';

module.exports = function (grunt) {
    require('time-grunt')(grunt);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        jshint: {
            all: [
                'Gruntfile.js',
                'src/**/*.js',
                '!src/**/lib/**/*.js',
                '!src/**/DataGridBundle/**/*.js',
                '!src/**/NavigationBundle/**/*.js'
            ],
            options: {
                jshintrc: '.jshintrc',
                reporter: require('jshint-stylish')
            }
        },
        jscs: {
            all: [
                'Gruntfile.js',
                'src/**/*.js',
                '!src/**/lib/**/*.js',
                '!src/**/DataGridBundle/**/*.js',
                '!src/**/NavigationBundle/**/*.js'
            ],
            options: {
                config: '.jscsrc',
                reporter: require('jscs-stylish').path
            }
        },
        recess: {
            all: [
                'src/**/*.css',
                'src/**/*.less',
                '!src/**/lib/**/*.css',
                '!src/**/lib/**/*.less',
                '!src/Pim/Bundle/UIBundle/Resources/public/css/less/oro.less',
                '!src/Pim/Bundle/UIBundle/Resources/public/css/pim.less',
                '!src/Pim/Bundle/UIBundle/Resources/public/css/form.less',
                '!src/Pim/Bundle/UIBundle/Resources/public/css/flags.less'
            ],
            options: {
                strictPropertyOrder: false,
                noIDs:               false,
                noOverqualifying:    false
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

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-jasmine');
    grunt.loadNpmTasks('grunt-recess');
    grunt.loadNpmTasks('grunt-jscs');

    grunt.registerTask('test', ['jasmine']);
    grunt.registerTask('codestyle', ['jshint', 'jscs', 'recess']);
    grunt.registerTask('travis', ['codestyle', 'test']);
    grunt.registerTask('default', ['codestyle', 'test']);
};
