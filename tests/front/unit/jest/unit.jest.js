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
    'akeneoassetmanager/infrastructure/fetcher',
    'akeneoassetmanager/application/component/app/select2',
    'akeneoassetmanager/platform/component/common/checkbox',
    'akeneodataqualityinsights/react',
    'akeneoassetmanager/application/component/asset/list/completeness-filter',
    'akeneoassetmanager/application/component/asset/list/search-bar/search-field', // cannot trigger re-render
    'akeneoassetmanager/application/component/asset/list/search-bar', // cannot test hooks + asynchronous fetching
    'akeneoassetmanager/application/component/app',
    'akeneoassetmanager/application/hydrator/attribute.ts',
    'akeneoassetmanager/infrastructure/uploader',
    'akeneoassetmanager/domain/event',
    'akeneoassetmanager/application/event',
    'akeneoassetmanager/application/asset-upload/saver',
    'akeneoassetmanager/application/action/asset/router',
    'akeneoassetmanager/application/action/asset',
    'akeneoassetmanager/application/component/asset-family/edit/header',
    'akeneoreferenceentity/tools',

    'src/Akeneo/AssetManager/front/tools',
    'vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/UIBundle/Resources/public/lib',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/assets-collection/infrastructure/fetcher',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/platform/component/common/checkbox',
    'src/Akeneo/AssetManager/front/infrastructure/fetcher',
    'src/Akeneo/Pim/Automation/DataQualityInsights/front',
    'src/Akeneo/AssetManager/front/application/component/asset/index/completeness-filter',
    'src/Akeneo/AssetManager/front/application/component/asset/list/search-bar/search-field', // cannot test hooks + asynchronous fetching + cannot trigger re-render
    'src/Akeneo/AssetManager/front/application/component/asset/list/search-bar', // cannot test hooks + asynchronous fetching
    'src/Akeneo/AssetManager/front/application/component/app',
    'src/Akeneo/AssetManager/front/application/hydrator/attribute.ts',
    'src/Akeneo/AssetManager/front/infrastructure/uploader',
    'src/Akeneo/AssetManager/front/domain/event',
    'src/Akeneo/AssetManager/front/application/event',
    'src/Akeneo/AssetManager/front/application/asset-upload/saver',
    'src/Akeneo/AssetManager/front/application/action/asset/router',
    'src/Akeneo/AssetManager/front/application/action/asset',
    'src/Akeneo/AssetManager/front/application/component/asset-family/edit/header',
    'src/Akeneo/ReferenceEntity/front/tools',
    'src/Akeneo/ReferenceEntity/front/infrastructure',
    'src/Akeneo/ReferenceEntity/front/infrastructure/tools',
    'src/Akeneo/ReferenceEntity/tests',
    'src/Akeneo/AssetManager/front/infrastructure',
    'src/Akeneo/AssetManager/tests',
  ],
  coverageThreshold: {
    ...baseConfig.coverageThreshold,
    'src/Akeneo/AssetManager/': {
      statements: 100,
      functions: 100,
      lines: 100,
    },
    'src/Akeneo/ReferenceEntity/': {
      statements: 100,
      functions: 100,
      lines: 100,
    },
    'src/Akeneo/Pim/Enrichment/': {
      statements: 100,
      functions: 100,
      lines: 100,
    },
  },
};

module.exports = Object.assign({}, baseConfig, eeConfig);
