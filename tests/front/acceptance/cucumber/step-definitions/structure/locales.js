module.exports = function(cucumber) {
    const { Given } = cucumber;
    const createLocale = require('../../factory/locale');
    const  { answerJson, csvToArray } = require('../../tools');

    Given('the locales {string}', async function(csvLocaleCodes) {
        const locales = csvToArray(csvLocaleCodes).map(localeCode => createLocale(localeCode));
        this.page.on('request', request => {
            if (request.url().includes('/configuration/locale/rest')) {
                answerJson(request, locales);
            }
        });
    });
};
