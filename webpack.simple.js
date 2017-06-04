/* eslint-env es6 */

const webpack = require('webpack')
const path = require('path')
const _ = require('lodash')

const ContextReplacementPlugin = require('webpack/lib/ContextReplacementPlugin')
// const AddToContextPlugin = require('./frontend/add-context-plugin')
const requirePaths = require(path.resolve('web/js/require-paths'))
const utils = require('./frontend/requirejs-utils')
const importPaths = utils.getModulePaths(requirePaths, __dirname)
const customPaths = require('./frontend/custom-paths')
const globToRegex = require('glob-to-regexp');

const contextPaths = [
    'web/bundles/pim**/*.js',
    'web/bundles/oro**/*.js',
    'web/bundles/fosjsrouting/**/*.js',
    'web/bundles/pim**/*.html',
    'frontend/**/*.js',
    'web/dist/**/*.js',
    'testingshit/**/*.js',
    'node_modules/**/*.js',
    ..._.values(customPaths)
].map(glob => globToRegex(glob).toString().slice(2, -2))

const contextRegex = `/^.*(${contextPaths.join('|')})$/`

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
        alias: Object.assign(importPaths, {
          jquery: require.resolve('jquery')
        })
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
          },
            {
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
            },
            {
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
            },
            {
                test: path.resolve(__dirname, './frontend/require-polyfill.js'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'require'
                    }
                ]
            },
            {
                test: /\.js$/,
                use: [
                    {
                        loader: path.resolve(__dirname, 'frontend/config-loader'),
                        options: {}
                    }
                ]
            },

        ]
    },
    resolveLoader: {
        moduleExtensions: ['-loader']
    },
    plugins: [
              // new AddToContextPlugin(_.values(importPaths)),
        new webpack.ProvidePlugin({'_': 'underscore', 'Backbone': 'backbone', '$': 'jquery', 'jQuery': 'jquery'}),
        new webpack.DefinePlugin({'require.specified': 'require.resolve'}),
        new ContextReplacementPlugin(/.\/dynamic/, path.resolve('./')),
    ]
}
