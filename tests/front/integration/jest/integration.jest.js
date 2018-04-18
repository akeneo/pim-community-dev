const fs = require('fs');
const baseConfig = require(`${__dirname}/../../common/base.jest.json`);

const integrationConfig = {
  rootDir: process.cwd(),
  globalSetup: `${__dirname}/setup.js`,
  globalTeardown: `${__dirname}/teardown.js`,
  testEnvironment: `${__dirname}/environment.js`,
  testRegex: '(tests/front/integration)(.*)(integration)\.(jsx?|tsx?)$',
  collectCoverage: false,
  testPathIgnorePatterns: [
    'node_modules'
  ]
};

module.exports = Object.assign({}, baseConfig, integrationConfig);
