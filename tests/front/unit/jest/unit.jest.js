const baseConfig = require(`${__dirname}/../../../../vendor/akeneo/pim-community-dev/tests/front/unit/jest/unit.jest.js`);

const eeModuleNameMapperConfig = {
  'pimee/rule-manager': '<rootDir>/public/bundles/pimenterpriseui/js/product/rule-manager.js',
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
    'akeneoassetmanager/application/component/app/separator',
    'akeneoassetmanager/application/component/app/pill',
    'akeneoassetmanager/application/component/app/spacer',
    'akeneoassetmanager/application/component/app/button',
    'akeneoassetmanager/infrastructure/fetcher',
    'akeneoassetmanager/application/component/app/select2',
    'akeneoassetmanager/platform/component/common/checkbox',
    'akeneofranklininsights/react/application/action',
    'akeneoassetmanager/application/component/asset/index/completeness-filter',
    'akeneoassetmanager/application/component/asset/list/search-bar/search-field', // cannot trigger re-render
    'akeneoassetmanager/application/component/asset/list/search-bar', // cannot test hooks + asynchronous fetching
    'akeneoassetmanager/application/component/app/channel-switcher.tsx',
    'akeneoassetmanager/application/component/app/locale-switcher.tsx',
    'akeneoassetmanager/application/component/app/icon/',

    'akeneoassetmanager/application/hydrator/attribute.ts',

    'src/Akeneo/AssetManager/front/tools',
    'vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/UIBundle/Resources/public/lib',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/assets-collection/infrastructure/fetcher',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/application/component/app/separator',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/application/component/app/pill',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/application/component/app/spacer',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/platform/component/common/checkbox',
    'src/Akeneo/AssetManager/front/application/component/app/button',
    'src/Akeneo/AssetManager/front/infrastructure/fetcher',
    'src/Akeneo/AssetManager/front/application/component/app/select2',
    'src/Akeneo/Pim/Automation/FranklinInsights/Infrastructure/Symfony/Resources/public/react/application/action',
    'src/Akeneo/AssetManager/front/application/component/asset/index/completeness-filter',
    'src/Akeneo/AssetManager/front/application/component/asset/list/search-bar/search-field', // cannot test hooks + asynchronous fetching + cannot trigger re-render
    'src/Akeneo/AssetManager/front/application/component/asset/list/search-bar', // cannot test hooks + asynchronous fetching
    'src/Akeneo/AssetManager/front/application/component/app/channel-switcher.tsx',
    'src/Akeneo/AssetManager/front/application/component/app/locale-switcher.tsx',
    'src/Akeneo/AssetManager/front/application/component/app/icon/',
    'src/Akeneo/AssetManager/front/application/hydrator/attribute.ts',
  ],
};

module.exports = Object.assign({}, baseConfig, eeConfig);
