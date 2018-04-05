const process = require('process');
const webpack = require('webpack');
const path = require('path');
const _ = require('lodash');
const LiveReloadPlugin = require('webpack-livereload-plugin');
const isProd = process.argv && process.argv.indexOf('--env=prod') > -1;
const { moduleAliases, moduleConfigs, rootDir } = require('./webpack/manifest.json');

const babelPresets = [['babel-preset-env', {
    targets: {
        browsers: ['firefox >= 45']
    }
}]];

if (isProd) babelPresets.push('babel-preset-minify');

console.log('Starting webpack from', rootDir, 'in', isProd ? 'prod' : 'dev', 'mode');

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
        path.resolve(rootDir, './web/bundles/pimenrich/js/index.js')
    ],
    output: {
        path: path.resolve('./web/dist/'),
        publicPath: '/dist/',
        filename: '[name].min.js',
        chunkFilename: '[name].bundle.js'
    },
    devtool: 'source-map',
    resolve: {
        extensions: ['.js', '.ts', '.json', '.html'],
        cacheWithContext: false,
        symlinks: false,
        modules: [path.resolve(rootDir, './web/bundles'), 'node_modules'],
        alias: _.mapKeys(moduleAliases, (path, key) => `${key}$`)
    },
    module: {
        noParse: function(content) {
            return /underscore/.test(content);
        },
        rules: [
            // Inject the module config (to replace module.config() from requirejs)
            {
                test: /\.js$/,
                exclude: /\/node_modules\/|\/spec\//,
                use: [
                    {
                        loader: path.resolve(__dirname, 'webpack/config-loader'),
                        options: {
                            configMap: moduleConfigs
                        }
                    }
                ]
            },
            // Load html without needing to prefix the requires with 'text!'
            {
                test: /\.html$/,
                include: /web\/bundles/,
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
            {
                test: /node_modules\/summernote\/dist\/summernote.js/,
                use: [
                    {
                        loader: 'imports-loader',
                        options: 'require=>function(){}'
                    },
                    {
                        loader: 'imports-loader',
                        options: 'require.specified=>function(){}'
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
                test: path.resolve(__dirname, './webpack/require-polyfill.js'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'require'
                    }
                ]
            },
            // Process the pim webpack files with babel
            {
                test: /\.js$/,
                include: /(web\/bundles)/,
                exclude: /lib|node_modules|src|spec/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: babelPresets,
                        cacheDirectory: 'web/cache/babel'
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
        // Map modules to variables for global use
        new webpack.ProvidePlugin({'_': 'underscore', 'Backbone': 'backbone', '$': 'jquery', 'jQuery': 'jquery'}),

        // Ignore these directories when webpack watches for changes
        new webpack.WatchIgnorePlugin([
            path.resolve(rootDir, './node_modules'),
            path.resolve(rootDir, './app'),
            path.resolve(rootDir, './app/cache'),
            path.resolve(rootDir, './vendor')
        ]),

        // Inject live reload to auto refresh the page (hmr not compatible with our app)
        new LiveReloadPlugin({appendScriptTag: true, ignore: /node_modules/})
    ]
};
