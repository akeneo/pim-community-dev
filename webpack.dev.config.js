/* eslint-env es6 */

const webpack = require('webpack')
const path = require('path')
const config = require('./webpack.config.js')
// const UglifyJSPlugin = require('uglifyjs-webpack-plugin')
// config.plugins.push(new UglifyJSPlugin())

config.entry = [
    'webpack-dev-server/client?http://localhost:4200/',
    'webpack/hot/only-dev-server',
    path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/index.js')
]

config.devtool = 'source-map'

config.watchOptions = {
    ignored: /node_modules/
}

config.output = {
    path: path.resolve('./web/dist/'),
    publicPath: 'http://localhost:4200/',
    filename: 'app.min.js',
    chunkFilename: '[name].bundle.js',
    pathinfo: true,
    devtoolLineToLine: true,
    hotUpdateChunkFilename: 'hot/hot-update.js',
    hotUpdateMainFilename: 'hot/hot-update.json'
}

config.devServer = {
    hot: true,
    publicPath: 'http://localhost:4200/',
    port: 4200,
    host: 'localhost',
    contentBase: './',
    inline: true,
    disableHostCheck: true,
    headers: {
        'Access-Control-Allow-Origin': '*'
    }
}

config.plugins.push(new webpack.HotModuleReplacementPlugin())

module.exports = config
