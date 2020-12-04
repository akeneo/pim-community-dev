const baseConfig = require(`${__dirname}/../../../../../../../../tests/front/unit/jest/unit.jest.js`);

const unitConfig = {
  ...baseConfig,
  coveragePathIgnorePatterns: ['src/Akeneo/Pim/Automation/DataQualityInsights'],
  verbose: true,
  testRegex: 'src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit(.*)(unit).(jsx?|tsx?)$',
};

module.exports = Object.assign({}, baseConfig, unitConfig);
