/* eslint-env es6 */
const fs = require('fs');
const process = require('process');
const rootDir = process.cwd();
const webpack = require('webpack');
const path = require('path');
const _ = require('lodash');


const WebpackShellPlugin = require('webpack-shell-plugin');
const ExtraWatchWebpackPlugin = require('extra-watch-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const isProd = process.argv && process.argv.indexOf('--env=prod') > -1;
const {getModulePaths, createModuleRegistry} = require('./frontend/webpack/requirejs-utils');
const {aliases, config} = getModulePaths(rootDir, __dirname);

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
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: "vendor",
          filename: "vendor.min.js",
          chunks: "all"
        },
        main: {
          filename: 'main.min.js'
        }
      }
    },
    moduleIds: 'hashed',
    minimizer: [new TerserPlugin({
      cache: true,
      parallel: true,
      sourceMap: false,
      terserOptions: {
        ecma: 6,
        mangle: true,
        output: {
          comments: false,
        },
      },
    })]
  },
  mode: (isProd ? 'production' : 'development'),
  target: 'web',
  entry: ['babel-polyfill', path.resolve(rootDir, './public/bundles/pimui/js/index.js')],
  output: {
    path: path.resolve('./public/dist/'),
    publicPath: '/dist/',
    filename: '[name].min.js',
    chunkFilename: '[name].bundle.js',
  },
  devtool: 'source-map',
  resolve: {
    symlinks: false,
    alias: _.mapKeys(aliases, (path, key) => `${key}$`),
    modules: [path.resolve('./public/bundles'), path.resolve('./node_modules')],
    extensions: ['.js', '.json', '.ts', '.tsx']
  },
  module: {
    rules: [
      // Inject the module config (to replace module.config() from requirejs)
      {
        test: /\.js$/,
        exclude: /\/node_modules\/|\/spec\//,
        use: [
          {
            loader: path.resolve(__dirname, 'frontend/webpack/config-loader'),
            options: {
              aliases,
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
          'thread-loader',
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
        test: path.resolve(__dirname, './frontend/webpack/require-polyfill.js'),
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
        include: [/public\/bundles/, /webpack/, /spec/, /node_modules\/p\-queue/],
        use: [
          'thread-loader',
          {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env'],
              cacheDirectory: 'public/cache',
            },
          }
        ],
      },

      {
        test: /\.(svg)$/,
        loader: 'file-loader',
        options: {
          outputPath: 'assets'
        },
      },

      // Process the typescript loader files
      {
        test: /\.tsx?$/,
        use: [
          {
            loader: 'ts-loader',
            options: {
              configFile: path.resolve(rootDir, 'tsconfig.json'),
              context: path.resolve(rootDir),
            },
          },
          {
            loader: path.resolve(__dirname, 'frontend/webpack/config-loader'),
            options: {
              aliases,
              configMap: config,
            },
          },
        ],
        include: /(public\/bundles)/,
        exclude: [
          path.resolve(rootDir, 'node_modules'),
          path.resolve(rootDir, 'vendor'),
          path.resolve(rootDir, 'tests'),
          path.resolve(__dirname, 'tests'),
          path.resolve(rootDir, 'src')
        ],
      },

      {
        test: /\.css$/,
        include: /node_modules/,
        loaders: ['style-loader', 'css-loader'],
      },
    ],
  },

  watchOptions: {
    ignored: /node_modules|var\/cache|vendor/,
  },

  plugins: [
    new WebpackShellPlugin({
      onBuildStart: ['yarn run less', 'yarn update-extensions'],
      dev: false
    }),

    new ExtraWatchWebpackPlugin({
      files: ['src/**/*{form_extensions/**/*.yml,form_extensions.yml}'],
    }),

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
      'process.env.EDITION': JSON.stringify(process.env.EDITION),
    }),
  ],
};

module.exports = webpackConfig;
