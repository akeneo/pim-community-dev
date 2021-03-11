module.exports = {
    preset: 'ts-jest',
    globals: {
        'ts-jest': {
            tsconfig: './tests/tsconfig.json',
        },
    },
    moduleDirectories: ['<rootDir>/../../../../../node_modules/'],
    moduleNameMapper: {
        '\\.(svg|css)$': '<rootDir>/tests/mocks/file-mock.ts',
        '^@src/(.*)$': '<rootDir>/src/$1',
    },
    setupFiles: ['./tests/mocks/fetch-mock.ts'],
    collectCoverageFrom: ['<rootDir>/src/**/*.{ts,tsx}'],
};
