var fs = require('fs');

const baseConfig = JSON.parse(fs.readFileSync('webpack/test/base.jest.json', 'utf8'));

const unitConfig = {
  "globals": {
    "__moduleConfig": {}
  },
  "setupFiles": [
    "./webpack/test/unit/enzyme.js"
  ],
  "testRegex": "(/__tests__/.*|(\\.|/)(unit))\\.(jsx?|tsx?)$"
};

module.exports = Object.assign({}, baseConfig, unitConfig);
