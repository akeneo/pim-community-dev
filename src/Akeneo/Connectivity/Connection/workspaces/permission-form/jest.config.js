module.exports = {
    preset: 'ts-jest',
    globals: {
        'ts-jest': {
            tsconfig: './tsconfig.json',
        },
    },
    moduleDirectories: [
        '<rootDir>/../../../../../../node_modules/',
        '<rootDir>/../../../../../../public/bundles/',
    ],
    setupFiles: [
        './tests/jquery.ts',
    ],
    moduleNameMapper: {
        '\\.(svg|css)$': '<rootDir>/tests/file-mock.ts',
    },
    collectCoverageFrom: [
      '<rootDir>/src/**/*.{ts,tsx}',
    ],
    coverageThreshold: {
        global: {
            branches: 100,
            functions: 100,
            lines: 100,
            statements: 100,
        },
    }
};
