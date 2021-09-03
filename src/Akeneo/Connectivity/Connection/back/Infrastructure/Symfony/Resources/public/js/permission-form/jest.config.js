module.exports = {
    preset: 'ts-jest',
    globals: {
        'ts-jest': {
            tsconfig: './tsconfig.json',
        },
    },
    moduleDirectories: [
        '<rootDir>/../../../../../../../../../../../node_modules/',
        '<rootDir>/../../../../../../../../../../../public/bundles/',
    ],
    setupFiles: [
        './jquery.ts',
    ],
    moduleNameMapper: {
        '\\.(svg|css)$': '<rootDir>/file-mock.ts',
    },
    collectCoverageFrom: [
      '<rootDir>/src/**/*.{ts,tsx}',
    ],
    coverageThreshold: {
        global: {
            branches: 85,
            functions: 90,
            lines: 90,
            statements: 90,
        },
    }
};
