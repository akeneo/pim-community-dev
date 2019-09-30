const baseConfig = require(`${__dirname}/../../../../vendor/akeneo/pim-community-dev/tests/front/unit/jest/unit.jest.js`);

const eeModuleNameMapperConfig = {
  'pimee/rule-manager': '<rootDir>/web/bundles/pimenterpriseui/js/product/rule-manager.js',
};

const moduleNameMapperConfig = {
  ...baseConfig.moduleNameMapper,
  ...eeModuleNameMapperConfig,
};

const eeConfig = {
  ...baseConfig,
  moduleNameMapper: moduleNameMapperConfig,
  coveragePathIgnorePatterns: [
    ...baseConfig.coveragePathIgnorePatterns,
    'akeneoassetmanager/tools',
    'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher',
    'akeneopimenrichmentassetmanager/platform/component/common',
    'akeneoassetmanager/application/component/app/button',
    'akeneofranklininsights/react/application/action',
    'pimui/lib',
    'src/Akeneo/AssetManager/front/tools',
    'vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/UIBundle/Resources/public/lib',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/assets-collection/infrastructure/fetcher',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/platform/component/common/index',
    'src/Akeneo/AssetManager/front/application/component/app/button',
    'src/Akeneo/Pim/Automation/FranklinInsights/Infrastructure/Symfony/Resources/public/react/application/action'
  ],
};

module.exports = Object.assign({}, baseConfig, eeConfig);
