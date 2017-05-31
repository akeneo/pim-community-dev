/* eslint-env es6 */

const webpack = require('webpack')
const path = require('path')
const config = require('./webpack.config.js')
// const UglifyJSPlugin = require('uglifyjs-webpack-plugin')

// config.plugins.push(new UglifyJSPlugin())

//webpack-dev-server --host localhost --port 4200 --hot --inline --config webpack.dev.config.js --progress

config.entry = [
  'webpack-dev-server/client?http://localhost:8080/',
  'webpack/hot/only-dev-server',
  path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/index.js')
]

config.devtool = 'source-map'

config.watchOptions = {
  ignored: /node_modules/
}

config.output = {
  path: path.resolve('./web/dist/'),
  publicPath: 'http://localhost:8080/',
  filename: 'app.min.js',
  chunkFilename: '[name].bundle.js',
  pathinfo: true,
  devtoolLineToLine: true
}

config.devServer = {
  filename: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/index.js'),
  hot: true,
  contentBase: path.resolve('./'),
  publicPath: 'http://localhost:8080/',
  port: 8080,
  host: 'localhost',
  inline: true,
  disableHostCheck: true,
  headers: { "Access-Control-Allow-Origin": "*"}
}

config.plugins.push(new webpack.HotModuleReplacementPlugin())
// config.plugins.push(new webpack.NamedModulesPlugin())

module.exports = config
