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
    'pim/router': '<rootDir>/web/bundles/pimui/js/router.js',
    routing: '<rootDir>/web/bundles/pimui/js/fos-routing-wrapper.js',
    routes: '<rootDir>/web/js/routes.js',
  },
  testRegex: '(tests/front/unit)(.*)(unit).(jsx?|tsx?)$',
  moduleFileExtensions: ['ts', 'tsx', 'js', 'jsx', 'json', 'node'],
  moduleDirectories: ['node_modules', `<rootDir>/web/bundles/`],
  globals: {
    __moduleConfig: {},
    'ts-jest': {
      tsConfig: `${__dirname}/../../../../tsconfig.json`,
      isolatedModules: true,
    },
    fos: {Router: {setData: () => {}}},
  },
  coverageReporters: ['text-summary', 'html'],
  coverageDirectory: '<rootDir>/coverage/',
  coverageThreshold: {
    global: {
      statements: 100,
      functions: 100,
      lines: 100,
    },
  },
  setupFiles: [`${__dirname}/enzyme.js`],
};

module.exports = Object.assign({}, baseConfig, unitConfig);
