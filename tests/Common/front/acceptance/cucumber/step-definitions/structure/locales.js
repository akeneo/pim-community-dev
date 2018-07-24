module.exports = function(cucumber) {
  const { Given } = cucumber;
  const LocaleBuilder = require('../../../../common/builder/locale');
  const createLocale = (localeCode) => (new LocaleBuilder()).withCode(localeCode).build();
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
