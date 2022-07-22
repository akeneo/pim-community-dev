const baseConfig = require(`${__dirname}/../../../../vendor/akeneo/pim-community-dev/tests/front/unit/jest/unit.jest.js`);

const config = {
    ...baseConfig,
    coveragePathIgnorePatterns: ['front-packages', 'vendor'],
    testPathIgnorePatterns: ['/node_modules/', '/front-packages/', '/vendor/'],
    coverageThreshold: {},
};

module.exports = Object.assign({}, config);
