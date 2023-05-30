/* eslint-env es6 */
const process = require('process');
const rootDir = process.cwd();
const webpack = require('webpack');
const path = require('path');
const _ = require('lodash');

const ExtraWatchWebpackPlugin = require('extra-watch-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const isProd = process.argv && process.argv.indexOf('--env=prod') > -1;
const isStrict = process.env.STRICT === '1';
const {getModulePaths, createModuleRegistry} = require('./frontend/webpack/requirejs-utils');
const {aliases, config} = getModulePaths(rootDir, __dirname);

// Plugin to make style components more readable in debug mode
const createStyledComponentsTransformer = require('typescript-plugin-styled-components').default;
const styledComponentsTransformer = createStyledComponentsTransformer();

createModuleRegistry(Object.keys(aliases), rootDir);

console.log('Starting webpack from', rootDir, 'in', isProd ? 'prod' : 'dev', 'mode', isStrict ? 'with typechecking' : '');

const webpackConfig = {
  cache: isProd ? false : {type: 'filesystem'},
  stats: {
    hash: false,
    modules: false,
    timings: true,
    version: true,
  },
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendor',
          filename: 'vendor.min.js',
          chunks: 'all'
        },
        main: {
          filename: 'main.min.js'
        }
      }
    },
    moduleIds: 'deterministic',
    minimizer: [new TerserPlugin({
      parallel: true,
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
  snapshot: {
    managedPaths: [
      /^(\/node_modules\/(?!@akeneo))/,
    ],
  },
  devtool: 'source-map',
  resolve: {
    symlinks: false,
    alias: _.mapKeys(aliases, (path, key) => `${key}$`),
    fallback: {
      'path': require.resolve('path-browserify'),
    },
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
            options: {
              exposes: ['Backbone'],
            }
          },
        ],
      },
      {
        test: /node_modules\/backbone\/backbone.js/,
        use: [
          {
            loader: 'imports-loader',
            options: {
              wrapper: 'window',
            }
          },
        ],
      },
      {
        test: /public\/bundles\/pimui\/lib\/summernote\/summernote.js/,
        use: [
          {
            loader: 'imports-loader',
            options: {
              additionalCode: 'var require = function(){};require.specified = function(){};',
            },
          },
        ],
      },
      // Expose jQuery to window
      {
        test: /node_modules\/jquery\/dist\/jquery.js/,
        use: [
          {
            loader: 'expose-loader',
            options: {
              exposes: ['$', 'jQuery'],
            },
          },
        ],
      },
      // Expose the require-polyfill to window
      {
        test: path.resolve(__dirname, './frontend/webpack/require-polyfill.js'),
        use: [
          {
            loader: 'expose-loader',
            options: {
              exposes: ['require'],
            },
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
        test: /\.(svg|gif)$/,
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
              transpileOnly: !isStrict,
              configFile: path.resolve(rootDir, 'tsconfig.json'),
              context: path.resolve(rootDir),
              getCustomTransformers: () => ({ before: [styledComponentsTransformer] })
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
        include: [/(public\/bundles)/, /node_modules\/@akeneo/],
        exclude: [
          /* Exclude /node_modules/ except /@akeneo/ workspaces */
          /node_modules\/(?!@akeneo)/,
          path.resolve(rootDir, 'vendor'),
          path.resolve(rootDir, 'tests'),
          path.resolve(__dirname, 'tests'),
          path.resolve(rootDir, 'src'),
          /node_modules\/@testing-library/,
          /node_modules\/immutable/,
          /node_modules\/react-test-renderer/
        ],
      },

      {
        test: /\.css$/,
        include: /node_modules/,
        use: ['style-loader', 'css-loader'],
      },
    ],
  },

  watchOptions: {
    ignored: [
      '/node_modules\/(?!@akeneo)/',
      path.resolve(rootDir, './config'),
      path.resolve(rootDir, './tests'),
      path.resolve(rootDir, './var'),
      path.resolve(rootDir, './vendor'),
    ]
  },

  plugins: [
    new ExtraWatchWebpackPlugin({
      files: ['src/**/*{form_extensions/**/*.yml,form_extensions.yml}'],
    }),

    // Map modules to variables for global use
    new webpack.ProvidePlugin({_: 'underscore', Backbone: 'backbone', $: 'jquery', jQuery: 'jquery'}),
    new webpack.DefinePlugin({
      'process.env.NODE_ENV': isProd ? JSON.stringify('production') : JSON.stringify('development'),
      'process.env.EDITION': JSON.stringify(process.env.EDITION),
    }),
  ],
};

module.exports = webpackConfig;
