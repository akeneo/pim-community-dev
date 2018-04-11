module.exports = function(cucumber) {
    const {Then} = cucumber;
    const assert = require('assert');

    Then('the title of the page should be {string}', async function (string) {
        const titleElement = await this.page.waitForSelector('.AknTitleContainer-title');
        const pageTitle = await (await titleElement.getProperty('textContent')).jsonValue();
        assert.equal(pageTitle.trim(), string);
    });
};
