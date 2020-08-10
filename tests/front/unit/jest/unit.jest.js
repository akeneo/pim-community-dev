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
    'pimui/lib',
    'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher',
    'akeneopimenrichmentassetmanager/platform/component/common',
    'akeneoassetmanager/application/component/app/button',
    'akeneoassetmanager/application/component/asset/index/completeness-filter',
    'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/search-bar/search-field', // cannot trigger re-render
    'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/search-bar', // cannot test hooks + asynchronous fetching
    'akeneoassetmanager/application/component/app/channel-switcher.tsx',
    'akeneoassetmanager/application/component/app/locale-switcher.tsx',
    'akeneoassetmanager/application/component/app/icon/',

    'src/Akeneo/AssetManager/front/tools',
    'vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/UIBundle/Resources/public/lib',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/assets-collection/infrastructure/fetcher',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/platform/component/common',
    'src/Akeneo/AssetManager/front/application/component/app/button',
    'src/Akeneo/AssetManager/front/application/component/asset/index/completeness-filter',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/assets-collection/infrastructure/component/asset-picker/search-bar/search-field', // cannot test hooks + asynchronous fetching + cannot trigger re-render
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/assets-collection/infrastructure/component/asset-picker/search-bar', // cannot test hooks + asynchronous fetching
    'src/Akeneo/AssetManager/front/application/component/app/channel-switcher.tsx',
    'src/Akeneo/AssetManager/front/application/component/app/locale-switcher.tsx',
    'src/Akeneo/AssetManager/front/application/component/app/icon/',
  ],
};

module.exports = Object.assign({}, baseConfig, eeConfig);
