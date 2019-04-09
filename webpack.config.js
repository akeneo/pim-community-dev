/* eslint-env es6 */
const fs = require('fs');
const process = require('process');
const rootDir = process.cwd();
const webpack = require('webpack');
const path = require('path');
const _ = require('lodash');


const WebpackShellPlugin = require('webpack-shell-plugin');
const ExtraWatchWebpackPlugin = require('extra-watch-webpack-plugin');
const HtmlWebpackInlineSourcePlugin = require('html-webpack-inline-source-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const isProd = process.argv && process.argv.indexOf('--env=prod') > -1;
const isTest = process.argv && process.argv.indexOf('--env=test') > -1;
const {getModulePaths, createModuleRegistry} = require('./frontend/webpack/requirejs-utils');
const {aliases, config} = getModulePaths(rootDir, __dirname);

createModuleRegistry(Object.keys(aliases), rootDir);

console.log('Starting webpack from', rootDir, 'in', isProd ? 'prod' : 'dev', 'mode');

module.exports = env => {
  const webPackConf = {
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
          include: /(web\/bundles|webpack|spec)/,
          exclude: /lib|node_modules/,
          use: [
            'thread-loader',
            {
              loader: 'babel-loader',
              options: {
                presets: ['@babel/preset-env'],
                cacheDirectory: 'web/cache',
              },
            }
          ],
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
              loader: path.resolve(__dirname, 'frontend/webpack/config-loader'),
              options: {
                aliases,
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
      new WebpackShellPlugin({
        onBuildStart: ['yarn update-extensions'],
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
        'process.env.EDITION': undefined === env || !Object.prototype.hasOwnProperty.call(env, 'EDITION') ? JSON.stringify('') : JSON.stringify(env.EDITION),
      }),
    ],
  };

  if (isTest) {
    const webPackTestConf = Object.assign({}, webPackConf, {
      entry: ['babel-polyfill', path.resolve(__dirname, './tests/front/common/templates/index.js')],
      output: {
        path: path.resolve('./web/test_dist/'),
        publicPath: '/dist/',
        filename: '[name].min.js',
        chunkFilename: '[name].bundle.js',
      },
    });

    webPackTestConf.plugins.push(
      new HtmlWebpackPlugin({
        inject: 'head',
        template: path.resolve(__dirname, './tests/front/common/templates/index.html'),
        minify: {},
        inlineSource: '.(js)$',
      })
    );

    webPackTestConf.plugins.push(new HtmlWebpackInlineSourcePlugin());

    return webPackTestConf;
  }

  return webPackConf;
};

