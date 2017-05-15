/* eslint-env es6 */

const webpack = require('webpack')
const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')
const paths = require('./web/config/paths')
const ContextReplacementPlugin = require('webpack/lib/ContextReplacementPlugin')
const glob = require('glob')

// @TODO
// - Rewrite this file
// - Load the conig files as json
// - Add logging and error checking

const getImportPaths = () => {
    let paths = {}
    let originalPaths = {}
    const bundles = glob.sync('./src/**/*requirejs.yml', {
        ignore: './src/Oro/Bundle/RequireJSBundle/Tests/Unit/Fixtures/Resources/config/requirejs.yml'
    })

    for (const bundle of bundles) {
        try {
            const contents = fs.readFileSync(bundle, 'utf8')
            const bundlePaths = yaml.parse(contents).config.paths
            originalPaths = Object.assign(originalPaths, bundlePaths)
            replacePathSegments(bundlePaths, bundle)
            paths = Object.assign(paths, bundlePaths)
        } catch (e) {
            console.log('######################## ERROR ', bundle, e)
        }
    }

    return {
        originalPaths,
        paths
    }
}

const resolvedPaths = {
    pimanalytics: 'Pim/Bundle/Analytics',
    pimdashboard: 'Pim/Bundle/Dashboard',
    pimdatagrid: 'Pim/Bundle/DataGrid',
    pimenrich: 'Pim/Bundle/Enrich',
    pimimportexport: 'Pim/Bundle/ImportExport',
    pimnavigation: 'Pim/Bundle/Navigation',
    fosjsrouting: 'Pim/Bundle/Enrich',
    pimnotification: 'Pim/Bundle/Notification',
    pimreferencedata: 'Pim/Bundle/ReferenceData',
    pimui: 'Pim/Bundle/UI',
    pimuser: 'Pim/Bundle/User',
    oroconfig: 'Oro/Bundle/Config'
}

const replacePathSegments = (paths) => {
    for (const name in paths) {
        let loc = paths[name].split('/')
        const resolved = resolvedPaths[loc.shift()]
        loc.unshift(`${__dirname}/src/${resolved}Bundle/Resources/public`)
        paths[name] = loc.join('/')
    }

    return paths
}

const importedPaths = getImportPaths()

const importPaths = Object.assign(importedPaths.paths, {
    text: 'text-loader',
    'pimuser/js/init-signin': path.resolve(__dirname, './src/Pim/Bundle/UserBundle/Resources/public/js/init-signin'),
    'bootstrap-modal': path.resolve(__dirname, './src/Pim/Bundle/UIBundle/Resources/public/lib/bootstrap-modal'),
    summernote: path.resolve(__dirname, './node_modules/summernote/dist/summernote.min'),
    'translator-lib': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/lib/translator'),
    translator: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/translator'),
    'config': path.resolve(__dirname, './web/config/module-config'),
    'fos-routing-base': path.resolve(__dirname, './vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router'),
    routing: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/fos-routing-wrapper'),
    routes: path.resolve(__dirname, './web/js/routes'),
    'pim/datagrid-view-fetcher': path.resolve(__dirname, './src/Pim/Bundle/DataGridBundle/Resources/public/js/fetcher/datagrid-view-fetcher'),
    'controllers': path.resolve(__dirname, './web/config/controllers'),
    'require-polyfill': path.resolve(__dirname, './web/config/require-polyfill'),
    'pim-router': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/router'),
    'paths': path.resolve(__dirname, './web/config/paths'),
    'CodeMirror': path.resolve(__dirname, './node_modules/codemirror/lib/codemirror'),
    'fetcher-list': path.resolve(__dirname, './web/config/fetchers'),
    'require-context': path.resolve(__dirname, './web/config/require-context'),
    'general': path.resolve(__dirname, './web/config/general')
})


fs.writeFileSync('./web/config/paths.js', `module.exports = ${JSON.stringify(importedPaths.originalPaths)}`, 'utf8')

const getRelativePaths = (absolutePaths) => {
    const replacedPaths = {}
    for (let path in absolutePaths) {
        // Change this to just remove the /src/ if it's a pim dep
        replacedPaths[paths[path]] = absolutePaths[path].replace(__dirname + '/src/Pim/Bundle', './Pim/Bundle').replace(__dirname + '/src/Oro/Bundle', './Oro/Bundle')
    }

    return replacedPaths
}

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
          path.resolve(__dirname, './src/'),
          getRelativePaths(importPaths)
        )
    ]
}
