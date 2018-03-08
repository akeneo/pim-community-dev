module.exports = function(cucumber) {
    const {Then} = cucumber;
    const path = require('path')
    const {answerJson} = require(path.resolve(process.cwd(), './vendor/akeneo/pim-community-dev/tests/front/acceptance/cucumber/tools.js'));
    const assert = require('assert');

    Then('this thing should return true', async function () {
        await assert.equal(true, true)
    });
}

