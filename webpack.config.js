/* eslint-env es6 */

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

console.log('Start compile with webpack from', __dirname)

const getRelativePaths = (absolutePaths) => {
    let replacedPaths = {}
    let exportPaths = {}

    for (let absolutePath in absolutePaths) {
        const pathValue = absolutePaths[absolutePath]
        const currentPath = path.resolve('./')
        const replacedPath = path.relative(currentPath, pathValue)
        replacedPaths[pathValue] = replacedPath
        exportPaths[absolutePath] = replacedPath
    }


    return { replacedPaths, exportPaths }
}

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

    requirePaths.forEach((requirePath) => {
        try {
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

    return {
        config,
        modulePaths
    }
}

const requireConfig = getRequireConfig(requireConfigPaths)
const importedPaths = requireConfig.modulePaths
const generalConfig = requireConfig.config
const overrides = _.mapValues(pathOverrides, override => path.resolve(__dirname, override))
const importPaths = Object.assign(importedPaths, overrides, {
  backbone: require.resolve('backbone'),

  // Generated
  routes: path.resolve('web/js/routes'),
  general: path.resolve('web/dist/general'),
  paths: path.resolve('web/dist/paths'),
  'relative-paths': path.resolve('web/dist/relative-paths'),
  'fos-routing-base': path.resolve('vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router')
})

const exportModule = (dest, contents) => {
  fs.writeFileSync(`web/dist/${dest}`, `module.exports = ${contents}`, 'utf8')
}

const relativePaths = getRelativePaths(importPaths)

mkdirp('web/dist', function () {
    exportModule('general.js', JSON.stringify(generalConfig))
    exportModule('paths.js', JSON.stringify(importPaths))
    exportModule('relative-paths.js', JSON.stringify(relativePaths.exportPaths))
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
                use: [{
                    loader: path.resolve(__dirname, './config-loader'),
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
                test: require.resolve('backbone'),
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
                test: path.resolve(__dirname, './frontend/require-polyfill.js'),
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
            '$': 'jquery',
            'jQuery': 'jquery'
        }),
        new webpack.DefinePlugin({
            'require.specified': 'require.resolve'
        }),
        new ContextReplacementPlugin(
          /.\/dynamic/,
          path.resolve('./')
        ),
        new AddToContextPlugin(_.values(importPaths))
    ]
}
