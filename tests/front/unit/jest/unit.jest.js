var fs = require('fs');
const baseConfig = require(`${__dirname}/../../common/base.jest.json`);

const unitConfig = {
  rootDir: process.cwd(),
  transform: {
    '^.+\\.tsx?$': 'ts-jest',
  },
  moduleNameMapper: {
    '^require-context$': `${__dirname}/../../../../frontend/webpack/require-context.js`,
    '^module-registry$': `${__dirname}/../../../../web/js/module-registry.js`,
    'pim/fetcher-registry': '<rootDir>/web/bundles/pimui/js/fetcher/fetcher-registry.js',
  },
  testRegex: '(tests/front/unit)(.*)(unit).(jsx?|tsx?)$',
  moduleFileExtensions: ['ts', 'tsx', 'js', 'jsx', 'json', 'node'],
  moduleDirectories: ['<rootDir>/node_modules', `<rootDir>/web/bundles/`],
  globals: {
    __moduleConfig: {},
    'ts-jest': {
      tsConfig: `${__dirname}/../../../../tsconfig.json`,
      isolatedModules: true,
    },
  },
  coverageReporters: ['text-summary', 'html'],
  coverageDirectory: '<rootDir>/coverage/',
  coverageThreshold: {
    global: {
      statements: 95,
      functions: 95,
      lines: 95,
    },
  },
  setupFiles: [`${__dirname}/enzyme.js`],
};

module.exports = Object.assign({}, baseConfig, unitConfig);
