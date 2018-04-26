var fs = require('fs');
const baseConfig = require(`${__dirname}/../../common/base.jest.json`);

const unitConfig = {
  rootDir: process.cwd(),
  globals: {
    '__moduleConfig': {}
  },
  transform: {
    '^.+\\.tsx?$': 'ts-jest'
  },
  moduleNameMapper: {
    '^require-context$': '<rootDir>/webpack/require-context.js',
    '^module-registry$': '<rootDir>/web/js/module-registry.js'
  },
  testRegex: '(tests/front/unit)(.*)(unit)\.(jsx?|tsx?)$',
  'moduleFileExtensions': [
    'ts',
    'tsx',
    'js',
    'jsx',
    'json',
    'node'
  ],
  moduleDirectories: ['<rootDir>/node_modules', `<rootDir>/web/bundles/`],
  globals: {
    'ts-jest': {
      tsConfigFile: `${__dirname}/../../../../tsconfig.json`
    }
  }
};

module.exports = Object.assign({}, baseConfig, unitConfig);
