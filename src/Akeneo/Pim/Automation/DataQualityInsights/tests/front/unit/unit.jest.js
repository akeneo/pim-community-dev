const baseConfig = require(`${__dirname}/../../../../../../../../tests/front/unit/jest/unit.jest.js`);

const unitConfig = {
  ...baseConfig,
  coveragePathIgnorePatterns: ['src/Akeneo/Pim/Automation/DataQualityInsights'],
  verbose: true,
  testMatch: ['<rootDir>/src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/**/*.unit.(js|jsx|ts|tsx)'],
};

module.exports = Object.assign({}, baseConfig, unitConfig);
