const baseConfig = require(`${__dirname}/../../../../../../../../tests/front/unit/jest/unit.jest.js`);

const unitConfig = {
  ...baseConfig,
  coveragePathIgnorePatterns: ['src/Akeneo/Pim/Automation/DataQualityInsights'],
  verbose: true,
  testRegex: `${__dirname}(.*)(unit).(jsx?|tsx?)$`,
};

module.exports = Object.assign({}, baseConfig, unitConfig);
