/* eslint-env es6 */

require('colors')
const webpack = require('webpack')
const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')
const _ = require('lodash')
const mkdirp = require('mkdirp')
const deepMerge = require('merge-objects')
const ContextReplacementPlugin = require('webpack/lib/ContextReplacementPlugin')
const pathOverrides = require('./frontend/path-overrides')
const requireConfigPaths = require(path.resolve('web/js/require-config'))
const AddToContextPlugin = require('./frontend/add-context-plugin')
const SimpleProgressWebpackPlugin = require('simple-progress-webpack-plugin')

console.log('→ Begin compiling modules with webpack from'.green, __dirname)

const getAbsolutePaths = (relativePaths, configPath) => {
    const absolutePaths = {}

    for (let relativePathName in relativePaths) {
        let relativePath = relativePaths[relativePathName].split('/')
        relativePath.shift()
        const resourcePath = path.resolve(configPath, '../../')
        absolutePaths[relativePathName] = resourcePath + '/public/' + relativePath.join('/')
    }

    return absolutePaths
}

const getRequireConfig = (requirePaths) => {
    let modulePaths = {}
    let config = {}

    console.log('→ Grabbing requirejs.yml config from bundles'.green)

    requirePaths.forEach((requirePath) => {
        try {
            console.log(`    → ${requirePath}`.grey)
            const contents = fs.readFileSync(requirePath, 'utf8')
            const parsedFile = yaml.parse(contents)
            const bundlePaths = parsedFile.config.paths
            const bundleConfig = parsedFile.config.config
            const absolutePaths = getAbsolutePaths(bundlePaths, requirePath)
            modulePaths = deepMerge(modulePaths, absolutePaths)
            config = deepMerge(config, bundleConfig)
        } catch (e) {
            // console.log('###', requirePath)
        }
    })

    return {config, modulePaths}
}

const requireConfig = getRequireConfig(requireConfigPaths)
const importedPaths = requireConfig.modulePaths
const generalConfig = requireConfig.config
const overrides = _.mapValues(pathOverrides, override => path.resolve(__dirname, override))

const importPaths = Object.assign(importedPaths, overrides, {
    backbone: require.resolve('backbone'),
    routes: path.resolve('web/js/routes'),
    general: path.resolve('web/dist/general'),
    paths: path.resolve('web/dist/paths'),
    summernote: path.resolve('node_modules/summernote/dist/summernote.min.js'),
    'fos-routing-base': path.resolve('vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router')
})

console.log('→ Including paths for'.green, `(${_.size(importPaths)})`.bold, 'modules'.green)

const exportModule = (dest, contents) => {
    const fileName = `web/dist/${dest}`
    console.log(`    Exporting module to dist - ${fileName}`.grey)
    fs.writeFileSync(fileName, `module.exports = ${contents}`, 'utf8')
}

mkdirp('web/dist', function() {
    console.log('→ Starting to dump module config to dist'.green)
    exportModule('general.js', JSON.stringify(generalConfig))
    exportModule('paths.js', JSON.stringify(importPaths))
})

module.exports = {
    target: 'web',
    entry: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/index.js'),
    output: {
        path: path.resolve('./web/dist/'),
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
                use: [
                    {
                        loader: path.resolve(__dirname, './config-loader'),
                        options: {}
                    }
                ]
            }, {
                test: /\.html$/,
                use: [
                    {
                        loader: 'raw-loader',
                        options: {}
                    }
                ]
            }, {
                test: require.resolve('backbone'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'Backbone'
                    }
                ]
            }, {
                test: require.resolve('backbone'),
                use: 'imports-loader?this=>window'
            }, {
                test: path.resolve(__dirname, './src/Pim/Bundle/UIBundle/Resources/public/lib/jquery/jquery-1.10.2'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'jQuery'
                    }, {
                        loader: 'expose-loader',
                        options: '$'
                    }
                ]
            }, {
                test: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/app'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'PimApp'
                    }
                ]
            }, {
                test: path.resolve(__dirname, './frontend/require-polyfill.js'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'require'
                    }
                ]
            }

        ]
    },
    resolveLoader: {
        moduleExtensions: ['-loader']
    },
    plugins: [
        new webpack.ProvidePlugin({'_': 'underscore', 'Backbone': 'backbone', '$': 'jquery', 'jQuery': 'jquery'}),
        new webpack.DefinePlugin({'require.specified': 'require.resolve'}),
        new ContextReplacementPlugin(/.\/dynamic/, path.resolve('./')),
        new AddToContextPlugin(_.values(importPaths)),
        new SimpleProgressWebpackPlugin({
            format: 'expanded'
        })
    ]
}
