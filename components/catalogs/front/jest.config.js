module.exports = {
    preset: 'ts-jest',
    globals: {
        'ts-jest': {
            tsconfig: './tsconfig.json',
        },
    },
    moduleDirectories: [
        '<rootDir>/../../../node_modules/',
    ],
    setupFilesAfterEnv: [
        '<rootDir>/tests/setup.ts',
    ],
    automock: true,
    clearMocks: true,
    unmockedModulePathPatterns: [
        // Core libraries for running the tests
        'react',
        'styled-components',
        '@testing-library',
        'jest-fetch-mock',
        'react-query',
        // Components of the DSM are not exposed individually, it's a nightmare to mock
        'akeneo-design-system',
        // The following libraries should be mocked but this is not an easy task
        'draft-js',
        'react-draft-wysiwyg',
        'html-to-draftjs',
    ],
    moduleNameMapper: {
        '\\.(svg|css)$': '<rootDir>/tests/test-file-stub.ts',
    },
    collectCoverageFrom: [
      '<rootDir>/src/components/**/*.{ts,tsx}',
    ],
    coverageThreshold: {
        global: {
            branches: 0,
            functions: 100,
            lines: 100,
            statements: 100,
        },
    }
};
