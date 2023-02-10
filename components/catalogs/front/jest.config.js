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
    workerIdleMemoryLimit: '300MB'
};
