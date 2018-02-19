const {Given, Then} = require('cucumber');
const assert = require('assert');
const {createChannel, createLocale} = require('../fixtures');
const {answerJson, csvToArray} = require('../tools');

Given('the following channels with locales:', async function(rawChannels) {
  const channels = rawChannels.hashes().map(({code, locales}) => createChannel(code, csvToArray(locales)));
  this.page.on('request', request => {
    if (request.url().includes('/configuration/channel/rest')) {
      answerJson(request, channels);
    }
  });
});

Then('the locales {string}', async function(csvLocaleCodes) {
  const locales = csvToArray(csvLocaleCodes).map(localeCode => createLocale(localeCode));
  this.page.on('request', request => {
    if (request.url().includes('/configuration/locale/rest')) {
      answerJson(request, locales);
    }
  });
});

Then('the channel should be {string}', async function(expectedChannel) {
  const actualChannel = await this.page.evaluate(element => {
    return element.dataset.identifier;
  }, await this.page.waitFor('.channel-switcher .AknColumn-value'));

  assert.equal(actualChannel, expectedChannel);
});

Then('the locale list should be {string}', async function(expectedLocales) {
  const localeCodeList = csvToArray(expectedLocales);

  await this.page.waitFor('.locale-switcher .AknDropdown-menu .AknDropdown-menuLink');
  const localeElements = await this.page.$$('.locale-switcher .AknDropdown-menu .AknDropdown-menuLink');

  const actualLocales = await Promise.all(
    localeElements.map(
      async element => await this.page.evaluate(domElement => domElement.dataset.identifier, await element)
    )
  );

  assert.deepEqual(actualLocales, localeCodeList);
});

Then('the locale should be {string}', async function(expectedLocale) {
  const actualLocale = await this.page.evaluate(element => {
    return element.dataset.identifier;
  }, await this.page.waitFor('.locale-switcher .AknColumn-value'));

  assert.equal(actualLocale, expectedLocale);
});

Then('I switch the locale to {string}', async function(locale) {
  await this.page.waitFor('.locale-switcher .AknActionButton');
  await this.page.click('.locale-switcher .AknActionButton');
  await this.page.waitFor(`.locale-switcher .AknDropdown-menuLink[data-identifier="${locale}"]`);
  await this.page.click(`.locale-switcher .AknDropdown-menuLink[data-identifier="${locale}"]`);
});

Then('I switch the channel to {string}', async function(channel) {
  await this.page.waitFor('.channel-switcher .AknActionButton');
  await this.page.click('.channel-switcher .AknActionButton');
  await this.page.waitFor(`.channel-switcher .AknDropdown-menuLink[data-identifier="${channel}"]`);
  await this.page.click(`.channel-switcher .AknDropdown-menuLink[data-identifier="${channel}"]`);
});
