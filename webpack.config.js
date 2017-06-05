/* eslint-env es6 */
const process = require('process')
const webpack = require('webpack')
const path = require('path')
const _ = require('lodash')

const ContextReplacementPlugin = require('webpack/lib/ContextReplacementPlugin')
const requirePaths = require(path.resolve('web/js/require-paths'))
const AddToContextPlugin = require('./frontend/add-context-plugin')
const utils = require('./frontend/requirejs-utils')

const rootDir = process.cwd();
const moduleConfig = utils.getModulePaths(requirePaths, rootDir)
// const UglifyJSPlugin = require('uglifyjs-webpack-plugin')
const globToRegex = require('glob-to-regexp');
const customPaths = require('./frontend/custom-paths')
const LiveReloadPlugin = require('webpack-livereload-plugin');

const contextPaths = [
    'web/bundles',
    'web/bundles/pim**/*.js',
    'web/bundles/oro**/*.js',
    'web/bundles/fosjsrouting/**/*.js',
    'web/bundles/pim**/*.html',
    'frontend/**/*.js',
    'web/dist/**/*.js',
    ..._.values(customPaths)
].map(glob => globToRegex(glob).toString().slice(2, -2))

const contextRegex = `/^.*(${contextPaths.join('|')})$/`
const moduleAliases = Object.assign(moduleConfig.paths,
   _.mapValues(customPaths, custom => path.resolve(rootDir, custom)),
   {
     'require-polyfill': path.resolve(__dirname, './frontend/require-polyfill.js'),
     'require-context': path.resolve(__dirname, './frontend/require-context.js')
   }
 )

console.log('Start webpack from', rootDir)

module.exports = {
    target: 'web',
    entry: [
        path.resolve(rootDir, './web/bundles/pimenrich/js/index.js')
    ],
    output: {
        path: path.resolve('./web/dist/'),
        publicPath: '/dist/',
        filename: '[name].min.js',
        chunkFilename: '[name].bundle.js'
    },
    resolve: {
        symlinks: false,
        alias: moduleAliases
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
                    replaceWith: contextRegex
                }
            }, {
                test: /\.js$/,
                exclude: /node_modules|spec/,
                use: [
                    {
                        loader: path.resolve(__dirname, 'frontend/config-loader'),
                        options: {
                            configMap: moduleConfig.config
                        }
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
            },
            {
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
        new AddToContextPlugin(_.values(moduleConfig.paths), rootDir),
        new webpack.WatchIgnorePlugin([
            path.resolve(rootDir, './node_modules'),
            path.resolve(rootDir, './app'),
            path.resolve(rootDir, './vendor'),
        ]),
        new LiveReloadPlugin({
          appendScriptTag: true,
          ignore: /node_modules/
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'lib',
            minChunks: module => module.context && module.context.indexOf('lib') !== -1
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: module => module.context && module.context.indexOf('node_modules') !== -1
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'manifest'
        })
        // new UglifyJSPlugin()
    ]
}
