const webpack = require('webpack');
const path = require('path');
const {typescriptLoader, babelLoader} = require('./loaders');
const {findRequirejsConfigsFromSymfonyBundles, createModuleRegistry} = require('./utils/alias');

const context = process.cwd();

const bundlesPaths = require(path.join(context, 'public/js/require-paths.js'));
const {aliases, config: configMap} = findRequirejsConfigsFromSymfonyBundles(bundlesPaths);

const formExtensionsAliases = Object.entries(aliases).reduce((aliases, [alias, modulePath]) => {
    aliases[`${alias}$`] = modulePath;

    return aliases;
}, {});

const otherAliases = {
    'require-polyfill': path.resolve(__dirname, './utils/require-polyfill.js'),
    'require-context': path.resolve(__dirname, './utils/require-context.js'),
    'module-registry': path.resolve(context, './public/js/module-registry.js'),
    routes: path.resolve(context, 'public/js/routes.json'),
    'fos-routing-base': path.resolve(context, 'vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js'),
};

createModuleRegistry(Object.keys(aliases), context);

module.exports = {
    context,
    resolve: {
        extensions: ['.js', '.ts', '.tsx'],
        modules: ['public/bundles', 'node_modules'],
        alias: {...formExtensionsAliases, ...otherAliases},
        symlinks: false,
    },
    entry: {
        main: [path.resolve(__dirname, './utils/backbone-patch.js'), './public/bundles/pimui/js/index.js'],
        workspaces: ['@akeneo-pim-community/connectivity-connection'],
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                exclude: /node_modules(?!\/@akeneo-pim-community)/,
                use: [babelLoader, typescriptLoader],
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: [babelLoader],
            },
            {
                test: /\.html$/,
                loader: 'raw-loader',
            },
            {
                test: /\.svg$/,
                loader: 'file-loader',
                options: {
                    outputPath: 'assets',
                },
            },
            {
                /* Inject each module with his config defined in 'form_extensions.yml' files  */
                test: /\.js$/,
                exclude: /node_modules/,
                use: [
                    {
                        loader: path.resolve(__dirname, './utils/config-loader.js'),
                        options: {
                            aliases,
                            configMap,
                            // debug: true,
                        },
                    },
                ],
            },
            {
                /* Polyfill 'require' calls in twig templates */
                test: path.resolve(__dirname, './utils/require-polyfill.js'),
                use: [
                    {
                        loader: 'expose-loader',
                        options: 'require',
                    },
                ],
            },
            {
                /* Backbone v0.9 is too old, not compatible with AMD and need access to the global window object */
                test: require.resolve('backbone'),
                use: ['imports-loader?this=>window'],
            },
            {
                /* Summernote v0.6 is too old and use a non-existent API 'require.specified' */
                test: require.resolve('summernote'),
                use: [
                    {
                        loader: 'imports-loader',
                        options: 'require.specified=>function(){}',
                    },
                ],
            },
        ],
    },
    target: 'web',
    output: {
        filename: '[name].js',
        chunkFilename: '[name].js',
        publicPath: 'dist/',
        path: path.resolve(context, 'public/dist'),
    },
    optimization: {
        splitChunks: {
            cacheGroups: {
                workspaces: {
                    test: /\/node_modules\/@akeneo-pim-community\//,
                    name: 'workspaces',
                    chunks: 'all',
                    priority: 1,
                },
                vendors: {
                    test: /\/node_modules\//,
                    name: 'vendors',
                    chunks: 'all',
                },
            },
        },
    },
    plugins: [
        new webpack.ProvidePlugin({
            _: 'underscore',
            $: 'jquery',
            Backbone: 'backbone',
            jQuery: 'jquery',
        }),
        new webpack.DefinePlugin({
            'process.env.EDITION': JSON.stringify(process.env.EDITION),
        }),
    ],
};
