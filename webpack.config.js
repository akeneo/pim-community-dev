const webpack = require('webpack')
const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')

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

// Import paths for resources and transform to json
// Also add oro bundles
// Add aliases for e.g. pimenrich, pimui etc..
const getImportPaths = () => {
    let paths = {}

    for (const bundle of bundles) {
        const configPath = path.join(__dirname, `/src/Pim/Bundle/${bundle}Bundle/Resources/config/requirejs.yml`)
        try {
            const contents = fs.readFileSync(configPath, 'utf8')
            const bundlePaths = yaml.parse(contents).config.paths
            const fixedBundlePaths = replacePathSegments(bundlePaths, bundle);
            paths = Object.assign(paths, bundlePaths)
        } catch (e) {}
    }
    return paths;
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
    return paths;
}

const importPaths = Object.assign(getImportPaths(), {
    text: 'text-loader',
    'pimuser/js/init-signin': path.resolve(__dirname, './src/Pim/Bundle/UserBundle/Resources/public/js/init-signin.js'),
    'bootstrap-modal': path.resolve(__dirname, './src/Pim/Bundle/UIBundle/Resources/public/lib/bootstrap-modal.js'),
    summernote: path.resolve(__dirname, './node_modules/summernote/dist/summernote.min.js'),
    translator: path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/lib/translator.js'),
    'module-config': path.resolve(__dirname, './src/Pim/Bundle/EnrichBundle/Resources/public/js/module-config.js'),
    routing: path.resolve(__dirname, './vendor/friendsofsymfony/jsrouting-bundle/Resources/js/router.js')
})

console.log(importPaths['routing'])

module.exports = {
    target: 'web',
    entry: './src/Pim/Bundle/EnrichBundle/Resources/public/js/app.js',
    output: {
        publicPath: path.resolve(__dirname, './web/'),
        filename: './web/app.min.js',
        pathinfo: true
    },
    resolve: {
        alias: importPaths
    },
    module: {
        loaders: [
            {
                loaders: ['closure-loader'],
                exclude: [/node_modules/, /test/]
            }, {
                test: /node_modules\/google-closure-library\/closure\/goog\/base/,
                loaders: ['imports-loader?this=>{goog:{}}&goog=>this.goog', 'exports-loader?goog']
            },
            {
                test : /node_modules\/google-closure-library\/closure\/goog\/.*\.js/,
                loaders : [require.resolve('./node_modules/closure-loader/index.js')],
                exclude : [/node_modules\/google-closure-library\/closure\/goog\/base\.js$/]
            }
        ]
    },

    plugins: [
        new webpack.ProvidePlugin({'window.Routing': 'routing', goog: 'google-closure-library/closure/goog/base'}),
        new webpack.LoaderOptionsPlugin({
            options: {
                closureLoader: {
                    paths: [
                        __dirname + '/node_modules/google-closure-library/closure/goog/',
                    ],
                    watch: false,
                }
            }
        })
    ]
}
