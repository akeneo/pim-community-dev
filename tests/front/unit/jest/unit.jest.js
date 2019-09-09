var fs = require('fs');
const baseConfig = require(`${__dirname}/../../../../vendor/akeneo/pim-community-dev/tests/front/unit/jest/unit.jest.js`);

const eeModuleNameMapperConfig = {
  'pimee/rule-manager': '<rootDir>/web/bundles/pimenterpriseui/js/product/rule-manager.js'
};

const moduleNameMapperConfig = {
  ...baseConfig.moduleNameMapper,
  ...eeModuleNameMapperConfig
};

const eeConfig = {
  ...baseConfig,
  moduleNameMapper: moduleNameMapperConfig
};

module.exports = Object.assign({}, baseConfig, eeConfig);
