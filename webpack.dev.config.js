/* eslint-env es6 */

const config = require('./webpack.config.js')
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');

config.plugins.push(new UglifyJSPlugin())

module.exports = config
