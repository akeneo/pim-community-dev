const webpack = require('webpack')
const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')
const paths = require('./web/js/paths')
const ContextReplacementPlugin = require("webpack/lib/ContextReplacementPlugin")

// traverse with filewalker automatically later for any bundles
const bundles = [
    'Analytics',
    'Api',
    'Catalog',
    'Comment',
    'Connector',
    'Dashboard',
    'DataGrid',
    'Enrich',
    'Filter',
    'ImportExport',
    'Installer',
    'Localization',
    'Navigation',
    'Notification',
    'PdfGenerator',
    'ReferenceData',
    'UI',
    'User',
    'Versioning'
]

const getImportPaths = () => {
    let paths = {}
    let originalPaths = {}

    for (const bundle of bundles) {
        // Use node-glob instead
        const configPath = path.join(__dirname, `/src/Pim/Bundle/${bundle}Bundle/Resources/config/requirejs.yml`)
        try {
            const contents = fs.readFileSync(configPath, 'utf8')
            const bundlePaths = yaml.parse(contents).config.paths
            originalPaths = Object.assign(originalPaths, bundlePaths)

            const fixedBundlePaths = replacePathSegments(bundlePaths, bundle)
            paths = Object.assign(paths, bundlePaths)
        } catch (e) {}
    }
    return {
        originalPaths,
        paths
    }
}

const getControllers = () => {
    const enrichConfig = fs.readFileSync(path.join(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/config/requirejs.yml'), 'utf8')
    return yaml.parse(enrichConfig).config.config['pim/controller-registry'].controllers
}

const getModuleConfigs = () => {
    // Get Resources/requirejs.yml:config:config of each bundle
}


// Use case converter and do it with code later
const resolvedPaths = {
    pimanalytics: 'Analytics',
    pimdashboard: 'Dashboard',
    pimdatagrid: 'DataGrid',
    pimenrich: 'Enrich',
    pimimportexport: 'ImportExport',
    pimnavigation: 'Navigation',
    fosjsrouting: 'Enrich',
    pimnotification: 'Notification',
    pimreferencedata: 'ReferenceData',
    pimui: 'UI',
    pimuser: 'User'
}

const replacePathSegments = (paths, bundle) => {
    for (const name in paths) {
        let loc = paths[name].split('/')
        const resolved = resolvedPaths[loc.shift()]
        loc.unshift(`${__dirname}/src/Pim/Bundle/${resolved}Bundle/Resources/public`)
        paths[name] = loc.join('/')
    }
    return paths
}

const importedPaths = getImportPaths()

const importPaths = Object.assign(importedPaths.paths, {
    text: 'text-loader',
    'pimuser/js/init-signin': path.resolve(__dirname, './src/Pim/Bundle/UserBundle/Resources/public/js/init-signin.js'),
    'bootstrap-modal': path.resolve(__dirname, './src/Pim/Bundle/UIBundle/Resources/public/lib/bootstrap-modal.js'),
    summernote: path.resolve(__dirname, './node_modules/summernote/dist/summernote.min.js'),
    translator: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/lib/translator.js'),
    'module-config': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/module-config.js'),
    'fos-routing-base': path.resolve(__dirname, './vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js'),
    routing: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/fos-routing-wrapper.js'),
    routes: path.resolve(__dirname, './web/js/routes.js'),
    'pim/datagrid-view-fetcher': path.resolve(__dirname, './src/Pim/Bundle/DataGridBundle/Resources/public/js/fetcher/datagrid-view-fetcher.js'),
    'fetchers': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/config/fetchers.js'),
    'controllers': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/config/controllers.js'),
    'require-polyfill': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/config/require-polyfill.js'),
    'pim-router': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/router.js'),
    'paths': path.resolve(__dirname, './web/js/paths.js'),
    'twig-dependencies': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/config/twig-dependencies.js'),
    'widget-dependencies': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/config/widget-dependencies.js'),
    'form-dependencies': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/config/form-dependencies.js'),
    'CodeMirror': path.resolve(__dirname, './node_modules/codemirror/lib/codemirror.js'),
    'fetcher-list': path.resolve(__dirname, './web/config/fetchers.js')
})

// console.log(importPaths['pim/family-edit-form/attributes/toolbar/add-select/attribute-group'])

fs.writeFileSync('./web/js/paths.js', `module.exports = ${JSON.stringify(importedPaths.originalPaths)}`, 'utf8')

const getRelativePaths = (absolutePaths) => {
    const replacedPaths = {}
    for ( let path in absolutePaths ) {
        replacedPaths[paths[path]] = absolutePaths[path].replace(__dirname + '/src/Pim/Bundle', '.')
    }
    return replacedPaths
}

module.exports = {
    target: 'web',
    entry: './src/Pim/Bundle/EnrichBundle/Resources/public/js/app.js',
    output: {
        path: path.resolve(__dirname, './web/'),
        publicPath: path.resolve(__dirname, '/'),
        filename: 'app.min.js',
        chunkFilename: '[name].bundle.js',
        pathinfo: true,
        devtoolLineToLine: true
    },
    resolve: {
        alias: importPaths,
    },
    module: {
        rules: [
            {
                test: /\.html$/,
                use: [ {
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
            // Expose with original path names instead
            {
                test: path.resolve(__dirname, './src/Pim/Bundle/DashboardBundle/Resources/public/js/widget-container.js'),
                use: [{
                    loader: 'expose-loader',
                    options: 'WidgetContainer'
                }]
            },
            {
                test: path.resolve(__dirname, './src/Pim/Bundle/DashboardBundle/Resources/public/js/last-operations-widget.js'),
                use: [{
                    loader: 'expose-loader',
                    options: 'LastOperationsWidget'
                }]
            },
            {
                test: path.resolve(__dirname, './src/Pim/Bundle/DashboardBundle/Resources/public/js/completeness-widget.js'),
                use: [{
                    loader: 'expose-loader',
                    options: 'CompletenessWidget'
                }]
            },
            {
                test: path.resolve(__dirname, './src/Pim/Bundle/AnalyticsBundle/Resources/public/js/patch-fetcher.js'),
                use: [{
                    loader: 'expose-loader',
                    options: 'PatchFetcher'
                }]
            },
            {
                test: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/config/require-polyfill.js'),
                use: [{
                    loader: 'expose-loader',
                    options: 'require'
                }]
            },

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
        }),
        // This is needed until summernote is updated
        new webpack.DefinePlugin({
            'require.specified': 'require.resolve',
        }),
        new ContextReplacementPlugin(
          /src\/Pim\/Bundle/,
          path.resolve(__dirname, './src/Pim/Bundle'),
          getRelativePaths(importPaths)
        //   {
        //       'pimdatagrid/js/fetcher/datagrid-view-fetcher': './DataGridBundle/Resources/public/js/fetcher/datagrid-view-fetcher.js'
        //   }
        ),
    ]
}
