/* eslint-env es6 */
const process = require('process')
const rootDir = process.cwd();
const webpack = require('webpack')
const path = require('path')
const _ = require('lodash')

const ContextReplacementPlugin = require('webpack/lib/ContextReplacementPlugin')
const utils = require('./frontend/requirejs-utils')

const requirePaths = require(path.resolve(rootDir, 'web/js/require-paths'))
const config = utils.getModulePaths(requirePaths, rootDir, __dirname)
const LiveReloadPlugin = require('webpack-livereload-plugin');
// const UglifyJSPlugin = require('uglifyjs-webpack-plugin')

console.log('Start webpack from', rootDir)

module.exports = {
    target: 'web',
    entry: [path.resolve(rootDir, './web/bundles/pimenrich/js/index.js')],
    output: {
        path: path.resolve('./web/dist/'),
        publicPath: '/dist/',
        filename: '[name].min.js',
        chunkFilename: '[name].bundle.js'
    },
    resolve: {
        symlinks: false,
        alias: config.aliases
    },
    // devtool: 'inline-source-map',
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
                    replaceWith: config.context
                }
            },
            {
                test: path.resolve(__dirname, 'frontend/require-context'),
                loader: 'regexp-replace-loader',
                options: {
                    match: {
                        pattern: /__contextPaths/,
                        flags: 'g'
                    },
                    replaceWith: JSON.stringify(config.paths)
                }
            },
            {
                test: /\.js$/,
                exclude: /node_modules|spec/,
                use: [
                    {
                        loader: path.resolve(__dirname, 'frontend/config-loader'),
                        options: {
                            configMap: config.config
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
                test: path.resolve(__dirname, './frontend/require-polyfill.js'),
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
        new ContextReplacementPlugin(/.\/dynamic/, path.resolve('./')),
        new require('./frontend/add-context-plugin')(
            _.values(config.paths),
            rootDir
        ),
        new webpack.WatchIgnorePlugin([
            path.resolve(rootDir, './node_modules'),
            path.resolve(rootDir, './app'),
            path.resolve(rootDir, './vendor')
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
