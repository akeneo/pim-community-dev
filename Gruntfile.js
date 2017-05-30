'use strict';

module.exports = function (grunt) {
    require('time-grunt')(grunt);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
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
        }
    });

    grunt.loadNpmTasks('grunt-recess');
    grunt.registerTask('default', ['recess']);
};
