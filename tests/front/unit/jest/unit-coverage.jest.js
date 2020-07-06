const baseConfig = require(`${__dirname}/unit.jest.js`);

const unitConfig = {
  collectCoverage: true,
  coveragePathIgnorePatterns: [
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/legacy-bridge',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared/src/components/Button.tsx',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared/src/components/NoData.tsx',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared/src/icons',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared/src/illustrations',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared/src/tools',
    'src/Akeneo/Tool/Bundle/MeasureBundle/Resources/public/shared/components/',
    'src/Akeneo/Tool/Bundle/MeasureBundle/Resources/public/shared/icons/',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared/tests/front/unit/utils.tsx',
    'src/Akeneo/Tool/Bundle/MeasureBundle/Resources/public/shared/illustrations/',
    'src/Akeneo/Tool/Bundle/MeasureBundle/Resources/public/pages/create-measurement-family/CreateMeasurementFamily.tsx',
    'src/Akeneo/Tool/Bundle/MeasureBundle/Resources/public/pages/create-unit/CreateUnit.tsx',
    'src/Akeneo/Platform/Bundle/CommunicationChannelBundle/front/src/components/icons',
    'src/Akeneo/Platform/Bundle/CommunicationChannelBundle/front/src/components/panel/announcement/Image.tsx',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/view',
    'src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/contexts',
    'src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/fetchers',
    'src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/store',
  ],
  coverageReporters: ['text-summary', 'html'],
  coverageDirectory: '<rootDir>/coverage/',
  coverageThreshold: {
    global: {
      statements: 100,
      functions: 100,
      lines: 100,
    },
  },
};

module.exports = Object.assign({}, baseConfig, unitConfig);
