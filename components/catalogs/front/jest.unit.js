module.exports = {
    preset: 'ts-jest',
    globals: {
        'ts-jest': {
            tsconfig: './tsconfig.json',
        },
    },
    clearMocks: true,
    testMatch: ['<rootDir>/src/**/*.unit.(ts|tsx)'],
    setupFilesAfterEnv: ['<rootDir>/tests/setup-unit.ts'],
    moduleDirectories: ['<rootDir>/../../../node_modules/'],
    moduleNameMapper: {
        '\\.(svg|css)$': '<rootDir>/tests/test-file-stub.ts',
    },
    coverageThreshold: {
        global: {
            branches: 100,
            functions: 100,
            lines: 100,
            statements: 100,
        },
    },
};
