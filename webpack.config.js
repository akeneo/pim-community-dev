/* eslint-env es6 */
const process = require('process')
const rootDir = process.cwd()
const webpack = require('webpack')
const { resolve } = require('path')
const { mapKeys } = require('lodash')
const { getModulePaths } = require('./frontend/requirejs-utils')
const { aliases, context, config, paths } = getModulePaths(rootDir, __dirname)
const isProd = process.argv && process.argv.indexOf('--env=prod') > -1

const WebpackCleanupPlugin = require('webpack-cleanup-plugin')
const LiveReloadPlugin = require('webpack-livereload-plugin')

const babelPresets = [['babel-preset-env', {
    targets: {
        browsers: ['firefox >= 45']
    }
}]];

if (isProd) babelPresets.push('babel-preset-minify')

console.log('Starting webpack from', rootDir, 'in environment', isProd ? 'prod' : 'dev')

module.exports = {
    stats: {
        hash: false,
        maxModules: 5,
        modules: false,
        timings: true,
        version: true
    },
    target: 'web',
    entry: [
        'babel-polyfill',
        resolve(rootDir, './web/bundles/pimenrich/js/index.js')
    ],
    output: {
        path: resolve('./web/dist/'),
        publicPath: '/dist/',
        filename: '[name].min.js',
        chunkFilename: '[name].bundle.js'
    },
    resolve: {
        symlinks: false,
        alias: mapKeys(aliases, (path, key) => `${key}$`)
    },
    module: {
        rules: [

            // Inject a regex that contains a list of the allowed paths to grab modules from
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

            // Inject the hash of absolute module paths mapped to module name
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

            // Inject the module config (to replace module.config() from requirejs)
            {
                test: /\.js$/,
                exclude: /\/node_modules\/|\/spec\//,
                use: [
                    {
                        loader: resolve(__dirname, 'frontend/config-loader'),
                        options: {
                            configMap: config
                        }
                    }
                ]
            },

            // Load html without needing to prefix the requires with 'text!'
            {
                test: /\.html$/,
                exclude: /node_modules|spec/,
                use: [
                    {
                        loader: 'raw-loader',
                        options: {}
                    }
                ]
            },

            // Expose the Backbone variable to window
            {
                test: /node_modules\/backbone\/backbone.js/,
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'Backbone'
                    }
                ]
            },
            {
                test: /node_modules\/backbone\/backbone.js/,
                use: [
                    {
                        loader: 'imports-loader',
                        options: 'this=>window'
                    }
                ]
            },

            // Expose jQuery to window
            {
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
            },

            // Expose the require-polyfill to window
            {
                test: resolve(__dirname, './frontend/require-polyfill.js'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'require'
                    }
                ]
            },


            // Process the pim frontend files with babel
            {
                test: /\.js$/,
                include: /(web\/bundles|frontend|spec)/,
                exclude: /lib|node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: babelPresets,
                        // Cache speeds up the incremental builds
                        cacheDirectory: 'web/cache'
                    }
                }
            }
        ]
    },

    watchOptions: {
        ignored: /node_modules|app|app\/cache|vendor/
    },

    // Support old loader declarations
    resolveLoader: {
        moduleExtensions: ['-loader']
    },
    plugins: [
        // Clean up the dist folder and source maps before rebuild
        new WebpackCleanupPlugin(),

        // Map modules to variables for global use
        new webpack.ProvidePlugin({'_': 'underscore', 'Backbone': 'backbone', '$': 'jquery', 'jQuery': 'jquery'}),

        // This is for the summernote lib (until it's updated to the latest version)
        new webpack.DefinePlugin({'require.specified': 'require.resolve'}),

        // Ignore these directories when webpack watches for changes
        new webpack.WatchIgnorePlugin([
            resolve(rootDir, './node_modules'),
            resolve(rootDir, './app'),
            resolve(rootDir, './app/cache'),
            resolve(rootDir, './vendor')
        ]),

        // Inject live reload to auto refresh the page (hmr not compatible with our app)
        new LiveReloadPlugin({appendScriptTag: true, ignore: /node_modules/}),

        // Split the app into chunks for performance
        new webpack.optimize.CommonsChunkPlugin({
            name: 'lib',
            minChunks: module => module.context && module.context.indexOf('lib') !== -1
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: module => module.context && module.context.indexOf('node_modules') !== -1
        }),
        new webpack.optimize.CommonsChunkPlugin({name: 'manifest'})
    ]
}
