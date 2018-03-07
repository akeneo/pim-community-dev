const dir = process.cwd();
const path = require('path');
const fs = require('fs');

const communityDir = "./vendor/akeneo/pim-community-dev/"

const runnerPath = path.resolve(dir, communityDir + 'webpack/test/acceptance/cucumber-runner');
const runner = require(runnerPath);
const cucumber = require('cucumber');

// console.log(fs.realpathSync(communityDir + 'tests/front/acceptance/cucumber/step-definitions/'));

runner(cucumber, [
    // fs.realpathSync(communityDir + 'tests/front/acceptance/cucumber/step-definitions/'),
    communityDir + 'tests/front/acceptance/cucumber/step-definitions/',
    './tests/front/acceptance/cucumber/step-definitions/'
]);