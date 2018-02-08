const {Given, Then} = require('cucumber');
const assert = require('assert');
const {channelList, productList} = require('./../fixtures');
const {random, json, spin} = require('./../tools');

Given(/^a product grid$/, async function() {
  await this.page.setRequestInterception(true);
  this.page.on('request', interceptedRequest => {
    if (interceptedRequest.url().includes('/configuration/channel/rest')) {
      random(() => {
        interceptedRequest.respond(json(channelList));
      });
    }
    if (interceptedRequest.url().includes('/enrich/product/rest/grid/') && interceptedRequest.url().includes('en_US')) {
      random(() => {
        interceptedRequest.respond(json(productList));
      });
    }
  });
  await this.page.evaluate(async () => {
    const bridge = require('pim/product/grid/bridge').default;

    await bridge(document.getElementById('app'));
  });

  await this.page.waitFor('.AknGridContainer');
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

Then('the product {string} of {string} should be {string}', async function(column, productIdentifier, expectedValue) {
  const row = await this.page.waitFor(`.AknGrid-bodyRow[data-identifier="${productIdentifier}"]`);
  const cell = await row.$(`.AknGrid-bodyCell[data-column="${column}"]`);
  const actualValue = await this.page.evaluate(element => {
    return element.innerHTML;
  }, cell);

  assert.equal(actualValue, expectedValue);
});
