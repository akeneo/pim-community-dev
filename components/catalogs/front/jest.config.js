module.exports = {
    projects: [
        {
            displayName: 'unit',
            ...require('./jest.unit'),
        },
        {
            displayName: 'integration',
            ...require('./jest.integration'),
        },
    ],
    collectCoverage: true,
    collectCoverageFrom: [
        '<rootDir>/src/**/use*.ts',
        '<rootDir>/src/**/*Reducer.ts',
        '<rootDir>/src/**/hooks/*.ts',
        '<rootDir>/src/**/reducers/*.ts',
        '<rootDir>/src/**/utils/*.ts',
    ],
    workerIdleMemoryLimit: '300MB'
};
