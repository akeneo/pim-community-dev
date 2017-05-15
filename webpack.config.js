/* eslint-env es6 */

const webpack = require('webpack')
const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')
const _ = require('lodash')
const ContextReplacementPlugin = require('webpack/lib/ContextReplacementPlugin')
const pathOverrides = require('./web/config/path-overrides')
const requireConfigPaths = require('./web/js/require-config')


const getAbsolute = (relativePaths, configPath) => {
    const absolutePaths = {}

    for (let relativePathName in relativePaths) {
        let relativePath = relativePaths[relativePathName].split('/')
        relativePath.shift()
        const resourcePath = path.resolve(configPath, '../../')
        absolutePaths[relativePathName] = resourcePath + '/public/' + relativePath.join('/')
    }

    return absolutePaths
}

const getPathsFromRequires = (requirePaths) => {
    let modulePaths = {}
    requirePaths.forEach((requirePath) => {
        try {
            const contents = fs.readFileSync(requirePath, 'utf8')
            const bundlePaths = yaml.parse(contents).config.paths
            const absolutePaths = getAbsolute(bundlePaths, requirePath)
            modulePaths = Object.assign(modulePaths, absolutePaths)
        } catch (e) { }
    })

    return modulePaths
}

const getRelativePaths = (absolutePaths) => {
    const replacedPaths = {}
    for (let absolutePath in absolutePaths) {
        const pathValue = absolutePaths[absolutePath]
        replacedPaths[pathValue] = pathValue.replace(__dirname + '/src', './')
    }

    return replacedPaths
}

const importedPaths = getPathsFromRequires(requireConfigPaths)
const overrides = _.mapValues(pathOverrides, override => path.resolve(override))
const importPaths = Object.assign(importedPaths, overrides)

fs.writeFileSync('./web/config/paths.js', `module.exports = ${JSON.stringify(importedPaths)}`, 'utf8')

module.exports = {
    target: 'web',
    entry: './src/Pim/Bundle/EnrichBundle/Resources/public/js/index.js',
    output: {
        path: path.resolve(__dirname, './web/dist/'),
        publicPath: 'dist/',
        filename: 'app.min.js',
        chunkFilename: '[name].bundle.js',
        pathinfo: true,
        devtoolLineToLine: true
    },
    resolve: {
        alias: importPaths
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: [{
                    loader: path.resolve('./config-loader'),
                    options: { }
                }]
            },
            {
                test: /\.html$/,
                use: [{
                    loader: 'raw-loader',
                    options: { }
                }]
            },
            {
                test: path.resolve(__dirname, './src/Pim/Bundle/UIBundle/Resources/public/lib/backbone/backbone.js'),
                use: 'imports-loader?this=>window'
            },
            {
                test: path.resolve(__dirname, './src/Pim/Bundle/UIBundle/Resources/public/lib/jquery/jquery-1.10.2'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'jQuery'
                    },
                    {
                        loader: 'expose-loader',
                        options: '$'
                    }
                ]
            },
            {
                test: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/app'),
                use: [{
                    loader: 'expose-loader',
                    options: 'PimApp'
                }]
            },
            {
                test: path.resolve(__dirname, './web/config/require-polyfill.js'),
                use: [{
                    loader: 'expose-loader',
                    options: 'require'
                }]
            }

        ]
    },
    resolveLoader: {
        moduleExtensions: ['-loader']
    },
    plugins: [
        new webpack.ProvidePlugin({
            '_': 'underscore',
            'Backbone': 'backbone',
            '$': 'jquery'
        }),
        // This is needed until summernote is updated
        new webpack.DefinePlugin({
            'require.specified': 'require.resolve'
        }),
        new ContextReplacementPlugin(
          /src/,
          path.resolve(__dirname, './src'),
          getRelativePaths(importPaths)
        )
    ]
}
