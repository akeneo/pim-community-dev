const path = require('path');

const ceHelpers = require(path.resolve(
  process.cwd(),
  './vendor/akeneo/pim-community-dev/tests/front/acceptance/test-helpers.js'
));

const eeHelpers = require(path.resolve(
  process.cwd(),
  './src/Akeneo/AssetManager/tests/front/acceptance/cucumber/tools.js'
));

module.exports = {
  ...ceHelpers,
  tools: {...ceHelpers.tools, ...eeHelpers},
};
