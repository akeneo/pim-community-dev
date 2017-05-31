/* eslint-env es6 */

const webpack = require('webpack')
const path = require('path')
const config = require('./webpack.config.js')
// const UglifyJSPlugin = require('uglifyjs-webpack-plugin')

// config.plugins.push(new UglifyJSPlugin())

//webpack-dev-server --host 127.0.0.1 --port 4200 --hot --inline --config webpack.dev.config.js --progress

config.entry = [
  'webpack-dev-server/client?http://127.0.0.1:4200',
  'webpack/hot/only-dev-server',
  path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/index.js')
]

config.devtool = 'source-map'

config.watchOptions = {
  ignored: /node_modules/
}

config.devServer = {
  // watchContentBase: false,
  hot: true,
  // contentBase: path.resolve(__dirname, './src'),
  publicPath: '/dist/',
  port: 4200,
  host: '127.0.0.1',
  inline: true,
  disableHostCheck: true
}

config.plugins.push(new webpack.HotModuleReplacementPlugin())
// config.plugins.push(new webpack.NamedModulesPlugin())

// --host 127.0.0.1 --port 4200 --hot --inline
module.exports = config
