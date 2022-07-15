module.exports = {
    preset: 'ts-jest',
    globals: {
        'ts-jest': {
            tsconfig: './tsconfig.json',
        },
    },
    clearMocks: true,
    testMatch: ['<rootDir>/src/**/*.integration.(ts|tsx)'],
    setupFilesAfterEnv: ['<rootDir>/tests/setup-integration.ts'],
    moduleDirectories: ['<rootDir>/../../../node_modules/'],
    moduleNameMapper: {
        '\\.(svg|css)$': '<rootDir>/tests/test-file-stub.ts',
    },
};
