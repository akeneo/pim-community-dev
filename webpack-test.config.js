/* eslint-env es6 */
const path = require('path');
const HtmlWebpackInlineSourcePlugin = require('html-webpack-inline-source-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const prodConfig = require('./webpack.config.js');

const config = Object.assign({}, prodConfig, {
  entry: ['babel-polyfill', path.resolve(__dirname, './tests/front/common/templates/index.js')],
  output: {
    path: path.resolve('./public/test_dist/'),
    publicPath: '/dist/',
    filename: '[name].min.js',
    chunkFilename: '[name].bundle.js',
  },
});

config.plugins.push(
  new HtmlWebpackPlugin({
    inject: 'head',
    template: path.resolve(__dirname, './tests/front/common/templates/index.html'),
    minify: {},
    inlineSource: '.(js)$',
  })
);

config.plugins.push(new HtmlWebpackInlineSourcePlugin());

module.exports = config;
