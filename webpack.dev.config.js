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

config.output = {
  path: path.resolve('./web/dist/'),
  publicPath: '/dist/',
  filename: 'app.min.js',
  chunkFilename: '[name].bundle.js'
}

config.devtool = 'source-map'

config.devServer = {
  filename: 'app.min.js',
  hot: true,
  contentBase: path.resolve(__dirname, './web/dist/'),
  publicPath: '/dist/',
  port: 4200,
  host: '127.0.0.1',
  inline: true,
  disableHostCheck: true
}

config.plugins.unshift(new webpack.HotModuleReplacementPlugin())
config.plugins.unshift(new webpack.NamedModulesPlugin())

console.log(config)
module.exports = config
