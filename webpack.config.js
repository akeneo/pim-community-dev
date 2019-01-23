/* eslint-env es6 */
const fs = require('fs');
const process = require('process');
const rootDir = process.cwd();
const webpack = require('webpack');
const path = require('path');
const _ = require('lodash');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const isProd = process.argv && process.argv.indexOf('--env=prod') > -1;
const sourcePath = path.join(rootDir, 'web/js/require-paths.js');

if (!fs.existsSync(sourcePath)) {
  throw new Error(`The web/js/require-paths.js module does not exist - You need to run
    "bin/console pim:install" or "bin/console pim:installer:dump-require-paths" before
    running webpack \n`);
}

const {getModulePaths, createModuleRegistry} = require('./webpack/requirejs-utils');
const {aliases, config} = getModulePaths(rootDir, __dirname, sourcePath);

createModuleRegistry(Object.keys(aliases), rootDir);

console.log('Starting webpack from', rootDir, 'in', isProd ? 'prod' : 'dev', 'mode');

const webpackConfig = {
  stats: {
    hash: false,
    maxModules: 5,
    modules: false,
    timings: true,
    version: true,
  },
  optimization: {
    minimizer: [
      new UglifyJsPlugin({
      sourceMap: true,
      uglifyOptions: {
        ecma: 8,
        compress: {
          warnings: false,
        },
      },
    })],
  },
  mode: 'development',
  target: 'web',
  entry: ['babel-polyfill', path.resolve(rootDir, './web/bundles/pimui/js/index.js')],
  output: {
    path: path.resolve('./web/dist/'),
    publicPath: '/dist/',
    filename: '[name].min.js',
    chunkFilename: '[name].bundle.js',
  },
  devtool: 'source-map',
  resolve: {
    symlinks: false,
    alias: _.mapKeys(aliases, (path, key) => `${key}$`),
    modules: [path.resolve('./web/bundles'), path.resolve('./node_modules')],
    extensions: ['.js', '.json', '.ts', '.tsx'],
  },
  module: {
    rules: [
      // Inject the module config (to replace module.config() from requirejs)
      {
        test: /\.js$/,
        exclude: /\/node_modules\/|\/spec\//,
        use: [
          {
            loader: path.resolve(__dirname, 'webpack/config-loader'),
            options: {
              configMap: config,
            },
          },
        ],
      },

      // Load html without needing to prefix the requires with 'text!'
      {
        test: /\.html$/,
        exclude: /node_modules|spec/,
        use: [
          {
            loader: 'raw-loader',
            options: {},
          },
        ],
      },

      // Expose the Backbone variable to window
      {
        test: /node_modules\/backbone\/backbone.js/,
        use: [
          {
            loader: 'expose-loader',
            options: 'Backbone',
          },
        ],
      },
      {
        test: /node_modules\/backbone\/backbone.js/,
        use: [
          {
            loader: 'imports-loader',
            options: 'this=>window',
          },
        ],
      },
      {
        test: /node_modules\/summernote\/dist\/summernote.js/,
        use: [
          {
            loader: 'imports-loader',
            options: 'require=>function(){}',
          },
          {
            loader: 'imports-loader',
            options: 'require.specified=>function(){}',
          },
        ],
      },
      // Expose jQuery to window
      {
        test: /node_modules\/jquery\/dist\/jquery.js/,
        use: [
          {
            loader: 'expose-loader',
            options: 'jQuery',
          },
          {
            loader: 'expose-loader',
            options: '$',
          },
        ],
      },

      // Expose the require-polyfill to window
      {
        test: path.resolve(__dirname, './webpack/require-polyfill.js'),
        use: [
          {
            loader: 'expose-loader',
            options: 'require',
          },
        ],
      },

      // Process the pim webpack files with babel
      {
        test: /\.js$/,
        include: /(web\/bundles|webpack|spec)/,
        exclude: /lib|node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
            cacheDirectory: 'web/cache',
          },
        },
      },

      // Process the typescript loader files
      {
        test: /\.tsx?$/,
        use: [
          {
            loader: 'ts-loader',
            options: {
              configFile: path.resolve(__dirname, 'tsconfig.json'),
              context: path.resolve(rootDir),
            },
          },
          {
            loader: path.resolve(__dirname, 'webpack/config-loader'),
            options: {
              configMap: config,
            },
          },
        ],
        include: /(web\/bundles)/,
        exclude: /lib|node_modules|vendor|tests|src|packages/,
      },
    ],
  },

  watchOptions: {
    ignored: /node_modules|var\/cache|vendor/,
  },

  plugins: [
    // Map modules to variables for global use
    new webpack.ProvidePlugin({_: 'underscore', Backbone: 'backbone', $: 'jquery', jQuery: 'jquery'}),

    // Ignore these directories when webpack watches for changes
    new webpack.WatchIgnorePlugin([
      path.resolve(rootDir, './node_modules'),
      path.resolve(rootDir, './app'),
      path.resolve(rootDir, './var'),
      path.resolve(rootDir, './vendor'),
    ]),

    new webpack.DefinePlugin({
      'process.env.NODE_ENV': isProd ? JSON.stringify('production') : JSON.stringify('development'),
    }),
  ],
};

module.exports = webpackConfig;
