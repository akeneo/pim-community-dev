/* eslint-env es6 */

const webpack = require('webpack')
const path = require('path')
const _ = require('lodash')

const ContextReplacementPlugin = require('webpack/lib/ContextReplacementPlugin')
const requirePaths = require(path.resolve('web/js/require-paths'))
const AddToContextPlugin = require('./frontend/add-context-plugin')
const utils = require('./frontend/requirejs-utils')
const importPaths = utils.getModulePaths(requirePaths, __dirname)
// const UglifyJSPlugin = require('uglifyjs-webpack-plugin')
const globToRegex = require('glob-to-regexp');
const customPaths = require('./frontend/custom-paths')

console.log(customPaths)

const contextPaths = [
    'web/bundles',
    'web/bundles/pim**/*.js',
    'web/bundles/oro**/*.js',
    'web/bundles/fosjsrouting/**/*.js',
    'web/bundles/pim**/*.html',
    'frontend/**/*.js',
    'web/dist/**/*.js',
    ..._.values(customPaths).map(custom => `${custom}`)
].map(glob => globToRegex(glob).toString().slice(2, -2))

const contextRegex = `/^.*(${contextPaths.join('|')})$/`
const moduleAliases = Object.assign(importPaths, _.mapValues(customPaths, custom => path.resolve(custom)))


module.exports = {
    target: 'web',
    entry: [
        path.resolve(__dirname, './web/bundles/pimenrich/js/index.js')
    ],
    output: {
        path: path.resolve('./web/dist/'),
        publicPath: '/dist/',
        filename: 'app.min.js',
        chunkFilename: '[name].bundle.js'
    },

    resolve: {
        symlinks: false,
        alias: moduleAliases
    },
    module: {
        rules: [
            {
                test: path.resolve(__dirname, 'frontend/require-context'),
                loader: 'regexp-replace-loader',
                options: {
                    match: {
                        pattern: /__contextPlaceholder/,
                        flags: 'g'
                    },
                    replaceWith: contextRegex
                }
            }, {
                test: /\.js$/,
                use: [
                    {
                        loader: path.resolve(__dirname, 'frontend/config-loader'),
                        options: {}
                    }
                ]
            }, {
                test: /\.html$/,
                use: [
                    {
                        loader: 'raw-loader',
                        options: {}
                    }
                ]
            }, {
                test: require.resolve('backbone'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'Backbone'
                    }
                ]
            }, {
                test: require.resolve('backbone'),
                use: [
                    {
                        loader: 'imports-loader',
                        options: 'this=>window'
                    }
                ]
            }, {
                test: require.resolve('jquery'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'jQuery'
                    }, {
                        loader: 'expose-loader',
                        options: '$'
                    }
                ]
            }, {
                test: path.resolve(__dirname, './frontend/require-polyfill.js'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'require'
                    }
                ]
            }

        ]
    },
    resolveLoader: {
        moduleExtensions: ['-loader']
    },
    plugins: [
        new webpack.ProvidePlugin({'_': 'underscore', 'Backbone': 'backbone', '$': 'jquery', 'jQuery': 'jquery'}),
        new webpack.DefinePlugin({'require.specified': 'require.resolve'}),
        new ContextReplacementPlugin(/.\/dynamic/, path.resolve('./')),
        new AddToContextPlugin(_.values(importPaths))
        // new UglifyJSPlugin()
    ]
}
