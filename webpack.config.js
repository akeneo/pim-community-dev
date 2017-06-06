/* eslint-env es6 */
const process = require('process')
const rootDir = process.cwd()
const webpack = require('webpack')
const { resolve } = require('path')
const { values } = require('lodash')

const ContextReplacementPlugin = require('webpack/lib/ContextReplacementPlugin')
const { getModulePaths } = require('./frontend/requirejs-utils')
const requirePaths = require(resolve(rootDir, 'web/js/require-paths'))
const { aliases, context, config, paths} = getModulePaths(requirePaths, rootDir, __dirname)
const AddToContextPlugin = require('./frontend/add-context-plugin')
const LiveReloadPlugin = require('webpack-livereload-plugin')
// const UglifyJSPlugin = require('uglifyjs-webpack-plugin')

console.log('Start webpack from', rootDir)

module.exports = {
    target: 'web',
    entry: [resolve(rootDir, './web/bundles/pimenrich/js/index.js')],
    output: {
        path: resolve('./web/dist/'),
        publicPath: '/dist/',
        filename: '[name].min.js',
        chunkFilename: '[name].bundle.js'
    },
    resolve: {
        symlinks: false,
        alias: aliases
    },
    // devtool: 'inline-source-map',
    module: {
        rules: [
            {
                test: resolve(__dirname, 'frontend/require-context'),
                loader: 'regexp-replace-loader',
                options: {
                    match: {
                        pattern: /__contextPlaceholder/,
                        flags: 'g'
                    },
                    replaceWith: context
                }
            },
            {
                test: resolve(__dirname, 'frontend/require-context'),
                loader: 'regexp-replace-loader',
                options: {
                    match: {
                        pattern: /__contextPaths/,
                        flags: 'g'
                    },
                    replaceWith: JSON.stringify(paths)
                }
            },
            {
                test: /\.js$/,
                exclude: /node_modules|spec/,
                use: [
                    {
                        loader: resolve(__dirname, 'frontend/config-loader'),
                        options: {
                            configMap: config
                        }
                    }
                ]
            }, {
                test: /\.html$/,
                exclude: /node_modules|spec/,
                use: [
                    {
                        loader: 'raw-loader',
                        options: {}
                    }
                ]
            }, {
                test: /node_modules\/backbone\/backbone.js/,
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'Backbone'
                    }
                ]
            }, {
                test: /node_modules\/backbone\/backbone.js/,
                use: [
                    {
                        loader: 'imports-loader',
                        options: 'this=>window'
                    }
                ]
            }, {
                test: /node_modules\/jquery\/dist\/jquery.js/,
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
                test: resolve(__dirname, './frontend/require-polyfill.js'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'require'
                    }
                ]
            }, {
                test: /\.js$/,
                include: /(web\/bundles|frontend|spec)/,
                exclude: /lib/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['es2017'],
                        cacheDirectory: 'web/cache'
                    }
                }
            }
        ]
    },
    resolveLoader: {
        moduleExtensions: ['-loader']
    },
    plugins: [
        new webpack.ProvidePlugin({'_': 'underscore', 'Backbone': 'backbone', '$': 'jquery', 'jQuery': 'jquery'}),
        new webpack.DefinePlugin({'require.specified': 'require.resolve'}),
        new ContextReplacementPlugin(/.\/dynamic/, resolve('./')),
        new AddToContextPlugin(
            values(paths),
            rootDir
        ),
        new webpack.WatchIgnorePlugin([
            resolve(rootDir, './node_modules'),
            resolve(rootDir, './app'),
            resolve(rootDir, './vendor')
        ]),
        new LiveReloadPlugin({appendScriptTag: true, ignore: /node_modules/}),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'lib',
            minChunks: module => module.context && module.context.indexOf('lib') !== -1
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: module => module.context && module.context.indexOf('node_modules') !== -1
        }),
        new webpack.optimize.CommonsChunkPlugin({name: 'manifest'})
        // new UglifyJSPlugin()
    ]
}
