const path = require('path');
const fs = require('fs');
const cucumber = require('cucumber');

const StepDictionary = require('step-dictionary');
const world = path.resolve(process.cwd(), './vendor/akeneo/pim-community-dev/tests/front/acceptance/cucumber/world.js');
const community = path.resolve(process.cwd(), './vendor/akeneo/pim-community-dev/tests/front/acceptance/cucumber/step-definitions')
const dictionary = new StepDictionary(community);

require(world)(cucumber)
dictionary.paths.forEach(file => require(file)(cucumber))

