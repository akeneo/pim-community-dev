var fs = require('fs');

const baseConfig = JSON.parse(fs.readFileSync('webpack/test/base.jest.json', 'utf8'));

const integrationConfig = {
  globalSetup: './webpack/test/integration-setup.js',
  globalTeardown: './webpack/test/integration-teardown.js',
  testEnvironment: './webpack/test/puppeteer-environment.js',
  testRegex: '(/__tests__/.*|(\\.|/)(integration))\\.(jsx?|tsx?)$',
  collectCoverage: false,
};

module.exports = Object.assign({}, baseConfig, integrationConfig);
