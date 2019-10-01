const path = require('path');
const cucumber = require('cucumber');
const base = process.cwd();
const glob = require('glob');

const world = path.resolve(base, './vendor/akeneo/pim-community-dev/tests/front/acceptance/cucumber/world.js');
const enterpriseSteps = path.resolve(base, './tests/front/acceptance/cucumber/step-definitions');
const communitySteps = path.resolve(
  base,
  './vendor/akeneo/pim-community-dev/tests/front/acceptance/cucumber/step-definitions'
);
const assetManagerSteps = path.resolve(
  base,
  './src/Akeneo/Pim/Enrichment/AssetManager/tests/front/acceptance/cucumber/step-definitions'
);

require(world)(cucumber);
glob
  .sync(`{${enterpriseSteps},${communitySteps},${assetManagerSteps}}/**/*.js`)
  .forEach(file => require(file)(cucumber));
