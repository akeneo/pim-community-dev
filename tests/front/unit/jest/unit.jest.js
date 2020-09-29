var fs = require('fs');
const baseConfig = require(`${__dirname}/../../common/base.jest.json`);

const unitConfig = {
  rootDir: process.cwd(),
  transform: {
    '^.+\\.tsx?$': 'ts-jest',
  },
  coveragePathIgnorePatterns: [
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/legacy-bridge',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared/src/components/Button.tsx',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared/src/components/Checkbox.tsx',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared/src/components/Modal.tsx',
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
  moduleNameMapper: {
    '^require-context$': `${__dirname}/../../../../frontend/webpack/require-context.js`,
    '^module-registry$': `${__dirname}/../../../../public/js/module-registry.js`,
    'pim/fetcher-registry': '<rootDir>/public/bundles/pimui/js/fetcher/fetcher-registry.js',
    'pim/router': '<rootDir>/public/bundles/pimui/js/router.js',
    routing: '<rootDir>/public/bundles/pimui/js/fos-routing-wrapper.js',
    routes: '<rootDir>/public/js/routes.js',
  },
  testRegex: '(tests/front/unit)(.*)(unit).(jsx?|tsx?)$',
  moduleFileExtensions: ['ts', 'tsx', 'js', 'jsx', 'json', 'node'],
  moduleDirectories: ['node_modules', `<rootDir>/public/bundles/`],
  globals: {
    __moduleConfig: {},
    'ts-jest': {
      tsConfig: `${__dirname}/../../../../tsconfig.json`,
      isolatedModules: true,
    },
    fos: {Router: {setData: () => {}}},
  },
  coverageReporters: ['text-summary', 'html'],
  coverageDirectory: '<rootDir>/coverage/',
  coverageThreshold: {
    '**/Akeneo/Platform/Bundle/UIBundle/**': {
      statements: 100,
      functions: 100,
      lines: 100,
    },
    '**/Akeneo/Tool/Bundle/MeasureBundle/**': {
      statements: 100,
      functions: 100,
      lines: 100,
    },
  },
  setupFiles: [`${__dirname}/enzyme.js`, `${__dirname}/mocks.js`, `${__dirname}/fetchMock.ts`],
};

module.exports = Object.assign({}, baseConfig, unitConfig);
