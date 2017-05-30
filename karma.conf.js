const webpack = require('./webpack.config.js')

module.exports = function(config) {
    config.set({
        files: [
            'frontend/jasmine-runner.js'
        ],

        preprocessors: {
            'frontend/jasmine-runner.js': ['webpack']
        },

        webpack,

        webpackMiddleware: {
            stats: 'errors-only'
        },

        plugins: [
            require('karma-webpack'),
            require('karma-jasmine'),
            require('karma-es6-shim'),
            require('karma-phantomjs-launcher'),
            require('karma-spec-reporter')
        ],

        frameworks: [
            'jasmine',
            'es6-shim'
        ],

        autoWatch: false,
        reporters: ['progress', 'spec'],
        browsers: ['PhantomJS']
    });
};
