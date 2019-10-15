module.exports = {
    preset: 'ts-jest',
    moduleNameMapper: {
        '\\.(svg)$': '<rootDir>/__mocks__/file.ts',
    },
    setupFiles: ['./enzyme.js', './jest-fetch-mock.ts'],
    timers: 'fake',
};
