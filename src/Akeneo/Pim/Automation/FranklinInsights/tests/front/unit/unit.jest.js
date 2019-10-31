const baseConfig = require(
  `${__dirname}/../../../../../../../../tests/front/unit/jest/unit.jest.js`
);

const unitConfig = {
  ...baseConfig,
  coveragePathIgnorePatterns: [
    'pimui/lib'
  ],
  verbose: true,
  testRegex: 'src/Akeneo/Pim/Automation/FranklinInsights/tests/front/unit(.*)(unit).(jsx?|tsx?)$',
};

module.exports = Object.assign({}, baseConfig, unitConfig);
