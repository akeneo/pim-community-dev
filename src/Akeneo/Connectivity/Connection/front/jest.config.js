module.exports = {
    preset: 'ts-jest',
    moduleNameMapper: {
        '\\.(svg)$': '<rootDir>/tests/mocks/file-mock.ts',
        '^@src/(.*)$': '<rootDir>/src/$1',
    },
    setupFiles: ['./tests/mocks/fetch-mock.ts'],
    collectCoverageFrom: ['<rootDir>/src/**/*.{ts,tsx}'],
};
