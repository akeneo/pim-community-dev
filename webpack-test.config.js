/* eslint-env es6 */
const path = require('path');
const HtmlWebpackInlineSourcePlugin = require('html-webpack-inline-source-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const prodConfig = require('./webpack.config.js');

const config = Object.assign({}, prodConfig, {
    entry: [
        'babel-polyfill',
        path.resolve(__dirname, './webpack/test/templates/index.js')
    ],
    output: {
        path: path.resolve('./web/test_dist/'),
        publicPath: '/dist/',
        filename: '[name].min.js',
        chunkFilename: '[name].bundle.js'
    }
});

config.plugins.push(new HtmlWebpackInlineSourcePlugin());
config.plugins.push(
    new HtmlWebpackPlugin({
        inject: 'head',
        template: path.resolve(__dirname, './webpack/test/templates/index.html'),
        minify: {},
        inlineSource: '.(js)$'
    }),
);

module.exports = config;
