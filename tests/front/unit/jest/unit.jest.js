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
    'pimui/lib',
    'akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher',
    'akeneodataqualityinsights/react',
    'akeneoreferenceentity/tools',

    //TODO RAC-591 add jest tests for the Permission tab in Asset manager & Ref entities
    'src/Akeneo/AssetManager/front/domain/model/asset-family/permission.ts',
    'src/Akeneo/ReferenceEntity/front/domain/model/reference-entity/permission.ts',

    'src/Akeneo/AssetManager/front/tools',
    'vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/UIBundle/Resources/public/lib',
    'src/Akeneo/Pim/Enrichment/AssetManager/Bundle/Resources/public/assets-collection/infrastructure/fetcher',
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
    'src/Akeneo/AssetManager/front/application/action/attribute',
    'src/Akeneo/AssetManager/front/application/component/asset-family/edit/attribute',
    'src/Akeneo/AssetManager/front/application/component/attribute',
    'src/Akeneo/AssetManager/front/application/component/attribute/create.tsx',
    'src/Akeneo/AssetManager/front/application/component/attribute/edit.tsx',
    'src/Akeneo/AssetManager/front/application/component/asset/list/filter/option',
    'src/Akeneo/ReferenceEntity/front/tools',
    'src/Akeneo/ReferenceEntity/front/infrastructure',
    'src/Akeneo/ReferenceEntity/front/infrastructure/tools',
    'src/Akeneo/ReferenceEntity/tests',
    'src/Akeneo/AssetManager/front/infrastructure',
    'src/Akeneo/AssetManager/tests',
  ],
  coverageThreshold: {
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
