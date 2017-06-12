/*eslint-env es6*/

const webpack = require('./webpack.config.js')
const path = require('path')
const _ = require('lodash')
const runnerPath = path.resolve(__dirname, 'frontend/jasmine-runner.js')

webpack.plugins = _.dropRight(webpack.plugins, 4)

module.exports = function(config) {
    config.set({
        files: [
            runnerPath
        ],

        preprocessors: {
            [runnerPath] : ['webpack']
        },

        webpack,

        webpackMiddleware: {
            stats: 'errors-only'
        },

        captureTimeout: 60000,
        browserDisconnectTimeout : 10000,
        browserDisconnectTolerance : 1,
        browserNoActivityTimeout : 60000,

        plugins: [
            require('karma-webpack'),
            require('karma-jasmine'),
            require('karma-es6-shim'),
            require('karma-phantomjs-launcher'),
            require('karma-spec-reporter'),
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
