const baseConfig = require('./jest.unit.config');

module.exports = {
  ...baseConfig,
  testMatch: ['**/?(*.)+(visual).ts?(x)'],
  preset: 'jest-puppeteer',
  collectCoverage: false,
  testTimeout: 30000
};
