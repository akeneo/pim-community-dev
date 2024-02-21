const path = require('path');
const cucumber = require('cucumber');
const base = process.cwd();
const glob = require('glob');
const stepsDirectory = path.resolve(base, './tests/front/acceptance/cucumber/step-definitions');
const worldFile = path.resolve(base, './tests/front/acceptance/cucumber/world.js');

require(worldFile)(cucumber);

glob.sync(`${stepsDirectory}/**/*.js`).forEach(file => require(file)(cucumber));
