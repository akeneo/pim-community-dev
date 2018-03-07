/* eslint-env es6 */
const rootDir = process.cwd();
const webpack = require('webpack');
const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const HtmlWebpackInlineSourcePlugin = require('html-webpack-inline-source-plugin');
const prodConfig = require('./webpack.config.js');
const WebpackCleanupPlugin = require('webpack-cleanup-plugin');
const LiveReloadPlugin = require('webpack-livereload-plugin');

module.exports = Object.assign({}, prodConfig, {
    entry: [
        'babel-polyfill',
        path.resolve(rootDir, './webpack/test/templates/index.js')
    ],
    output: {
        path: path.resolve('./web/test_dist/'),
        publicPath: '/dist/',
        filename: '[name].min.js',
        chunkFilename: '[name].bundle.js'
    },

    plugins: [
        // Clean up the dist folder and source maps before rebuild
        new WebpackCleanupPlugin(),
        new HtmlWebpackPlugin({
            inject: 'head',
            template: './webpack/test/templates/index.html',
            minify: {},
            inlineSource: '.(js)$'
        }),

        // Map modules to variables for global use
        new webpack.ProvidePlugin({'_': 'underscore', 'Backbone': 'backbone', '$': 'jquery', 'jQuery': 'jquery'}),

        // Ignore these directories when webpack watches for changes
        new webpack.WatchIgnorePlugin([
            path.resolve(rootDir, './node_modules'),
            path.resolve(rootDir, './app'),
            path.resolve(rootDir, './app/cache'),
            path.resolve(rootDir, './vendor')
        ]),

        // Split the app into chunks for performance
        new webpack.optimize.CommonsChunkPlugin({
            name: 'lib',
            minChunks: module => module.context && module.context.indexOf('lib') !== -1
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: module => module.context && module.context.indexOf('node_modules') !== -1
        }),
        new webpack.optimize.CommonsChunkPlugin({name: 'manifest'}),
        new HtmlWebpackInlineSourcePlugin()
    ]
});