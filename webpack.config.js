const webpack = require('webpack')
const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')
const camelCase = require('camelcase')

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
        } catch(e) {}
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

module.exports = {
    entry: './src/Pim/Bundle/EnrichBundle/Resources/public/js/app.js',
    output: {
        filename: './web/app.min.js'
    },
    // module: {
    //     rules: [
    //       { test: /\.(js)$/, use: 'babel-loader' }
    //     ]
    // },
    resolve: {
        alias: getImportPaths()
    },
    plugins: [
        new webpack.optimize.UglifyJsPlugin(),
    ]
}
