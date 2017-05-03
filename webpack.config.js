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
const getImportPaths = () => {
    let paths = {}

    for (const bundle of bundles) {
        const configPath = path.join(__dirname, `/src/Pim/Bundle/${bundle}Bundle/Resources/config/requirejs.yml`)
        // promises.push(getPathsFromConfig(configPath))
        try {
            const contents = fs.readFileSync(configPath, 'utf8')
            const bundlePaths = yaml.parse(contents).config.paths
            paths = Object.assign(paths, bundlePaths)
        } catch(e) {}
    }

    return paths;
}

// Search in /public/js for each bundle
// Include oro bundles

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
