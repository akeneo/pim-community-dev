module.exports = {
    preset: 'ts-jest',
    globals: {
        'ts-jest': {
            tsconfig: './tsconfig.json',
        },
    },
    testMatch: ['<rootDir>/src/**/*.test.(ts|tsx)'],
    setupFilesAfterEnv: ['<rootDir>/tests/setup-unit.ts'],
    moduleDirectories: ['<rootDir>/../../../node_modules/'],
    moduleNameMapper: {
        '\\.(svg|css)$': '<rootDir>/tests/test-file-stub.ts',
    },
    collectCoverage: true,
    collectCoverageFrom: [
        '<rootDir>/src/**/use*.ts',
        '<rootDir>/src/**/hooks/*.ts',
        '<rootDir>/src/**/*Reducer.ts',
        '<rootDir>/src/**/reducers/*.ts',
    ],
    coverageThreshold: {
        global: {
            branches: 100,
            functions: 100,
            lines: 100,
            statements: 100,
        },
    },
};
