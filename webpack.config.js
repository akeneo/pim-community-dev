/* eslint-disable */
const fs = require('fs');
const process = require('process');
const webpack = require('webpack');
const path = require('path');

const rootDir = process.cwd();

const TerserPlugin = require('terser-webpack-plugin');
const isProd = process.argv && process.argv.indexOf('--env=prod') > -1;
const isStrict = process.argv && process.argv.indexOf('--strict') > -1;
const {getModulePaths, createModuleRegistry} = require('./frontend/webpack/requirejs-utils');
const {aliases, config} = getModulePaths(rootDir, __dirname);

createModuleRegistry(Object.keys(aliases), rootDir);

const resolverAliases = {};
for (let [key, value] of Object.entries(aliases)){
  resolverAliases[`${key}$`] = value;
}

console.log('Starting webpack from', rootDir, 'in', isProd ? 'prod' : 'dev', 'mode');

const webpackConfig = {
  entry: ['babel-polyfill', path.resolve(rootDir, './public/bundles/pimui/js/index.js')],
  target: 'web',
  mode: (isProd ? 'production' : 'development'),
  context: path.resolve(rootDir),
  output: {
    path: path.resolve('./public/dist/'),
    publicPath: '/dist/',
    filename: '[name].min.js',
    chunkFilename: '[name].bundle.js',
  },
  stats: {
    hash: false,
    modules: false,
  },
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: /node_modules/,
          priority: -10,
          name: "vendor",
          filename: "[name].min.js",
          chunks: "all"
        },
        default: {
          priority: -20,
          reuseExistingChunk: true
        }
      }
    },
    minimize: isProd,
    minimizer: [
      new TerserPlugin({
        cache: true,
        parallel: true,
        sourceMap: !isProd,
        exclude: /node_modules/,
        extractComments: false,
        terserOptions: {
          output: {
            ecma: 2015,
            compress: true,
            mangle: true,
            comments: false,
          },
        },
      })
    ]
  },
  devtool: 'source-map',
  resolve: {
    symlinks: false,
    alias: resolverAliases,
    modules: [path.resolve('./public/bundles'), path.resolve('./node_modules')],
    extensions: ['.js', '.json', '.ts', '.tsx']
  },
  module: {
    rules: [
      // Inject the module config (to replace module.config() from requirejs)
      {
        test: /\.js$/,
        exclude: /node_modules\/(?!(p-queue))/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env'],
              cacheDirectory: true,
            }
          },
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

      // Expose the Backbone variable to window
      {
        test: /node_modules\/backbone\/backbone.js/,
        use: [
          {
            loader: 'imports-loader',
            options: 'this=>window',
          },
        ],
      },

      // Mock the 'require' used by summernote
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

      // Load the svg files
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
        exclude: /node_modules|spec/,
        use: [
          {
            loader: 'ts-loader',
            options: {
              transpileOnly: !(isProd || isStrict),
              experimentalWatchApi: true,
              onlyCompileBundledFiles: true,
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
      },

      // Load the css files
      {
        test: /\.css$/,
        use: [
          'style-loader',
          'css-loader'
        ]
      },
    ],
  },

  watchOptions: {
    ignored: /app|var|node_modules|vendor/,
  },

  plugins: [
    // Map modules to variables for global use
    new webpack.ProvidePlugin({_: 'underscore', Backbone: 'backbone', $: 'jquery', jQuery: 'jquery'}),

    new webpack.DefinePlugin({
      'process.env.NODE_ENV': isProd ? JSON.stringify('production') : JSON.stringify('development'),
      'process.env.EDITION': JSON.stringify(process.env.EDITION),
    }),
  ],
};

module.exports = webpackConfig;
