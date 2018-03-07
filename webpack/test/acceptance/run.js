const path = require('path');
const cucumber = require('cucumber');
const base = process.cwd();

const StepDictionary = require('step-dictionary');
const world = path.resolve(base, './tests/front/acceptance/cucumber/world.js');
const community = path.resolve(base, './tests/front/acceptance/cucumber/step-definitions');
const dictionary = new StepDictionary(community);

require(world)(cucumber);
dictionary.paths.forEach(file => require(file)(cucumber));

