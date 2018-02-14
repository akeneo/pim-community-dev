const {Given, Then} = require('cucumber');
const assert = require('assert');
const {createChannel} = require('../fixtures');
const {answerJson} = require('../tools');

Given('the following channels with locales:', async function(rawChannels) {
  const channels = rawChannels
    .hashes()
    .map(({code, locales}) => createChannel(code, locales.split(',').map(localeCode => localeCode.trim())));

  this.page.on('request', interceptedRequest => {
    if (interceptedRequest.url().includes('/configuration/channel/rest')) {
      answerJson(interceptedRequest, channels);
    }
  });
});

Then('the locale should be {string}', async function(expectedLocale) {
  const actualLocale = await this.page.evaluate(element => {
    return element.dataset.identifier;
  }, await this.page.waitFor('.locale-switcher .value'));

  assert.equal(actualLocale, expectedLocale);
});

Then('I switch the locale to {string}', async function(locale) {
  await this.page.waitFor('.locale-switcher .AknActionButton');
  await this.page.click('.locale-switcher .AknActionButton');
  await this.page.waitFor(`.locale-switcher .AknDropdown-menuLink[data-identifier="${locale}"]`);
  await this.page.click(`.locale-switcher .AknDropdown-menuLink[data-identifier="${locale}"]`);
});
