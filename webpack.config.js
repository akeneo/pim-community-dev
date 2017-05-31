/* eslint-env es6 */

const webpack = require('webpack')
const path = require('path')
const _ = require('lodash')

const ContextReplacementPlugin = require('webpack/lib/ContextReplacementPlugin')
const requirePaths = require(path.resolve('web/js/require-paths'))
const AddToContextPlugin = require('./frontend/add-context-plugin')
const pathOverrides = require('./frontend/path-overrides')
const utils = require('./frontend/requirejs-utils')
const importPaths = utils.getModulePaths(requirePaths, pathOverrides, __dirname)

module.exports = {
    target: 'web',
    entry: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/index.js'),
    output: {
        path: path.resolve('./web/dist/'),
        publicPath: '/dist/',
        filename: 'app.min.js',
        chunkFilename: '[name].bundle.js',
        pathinfo: true,
        devtoolLineToLine: true
    },
    resolve: {
        alias: importPaths
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: [
                    {
                        loader: path.resolve(__dirname, './config-loader'),
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
                use: 'imports-loader?this=>window'
            }, {
                test: path.resolve(__dirname, './src/Pim/Bundle/UIBundle/Resources/public/lib/jquery/jquery-1.10.2'),
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
    ]
}
