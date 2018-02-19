const {Given, Then} = require('cucumber');
const assert = require('assert');
const {createProductWithLabels} = require('../fixtures');
const {answerJson} = require('../tools');

var responseProducts = [];

Given(/^a product grid is displayed$/, async function() {
  await this.page.evaluate(async () => {
    const bridge = require('pim/product/grid/bridge').default;

    await bridge(document.getElementById('app'));
  });

  await this.page.waitFor('.AknGridContainer');
});

Given('the following product labels:', async function(products) {
  responseProducts = products.hashes().map(product => {
    const {identifier, ...labels} = product;

    return createProductWithLabels(identifier, labels);
  });

  this.page.on('request', request => {
    if (request.url().includes('/enrich/product/rest/grid/')) {
      answerJson(request, {items: responseProducts, total: responseProducts.length});
    }
  });
});

Then('the product {string} of {string} should be {string}', async function(column, productIdentifier, expectedValue) {
  const row = await this.page.waitFor(`.AknGrid-bodyRow[data-identifier="${productIdentifier}"]`);
  const cell = await row.$(`.AknGrid-bodyCell[data-column="${column}"]`);
  const actualValue = await this.page.evaluate(element => {
    return element.innerHTML;
  }, cell);

  assert.equal(actualValue, expectedValue);
});

Then('I should see the loading indicator', async function() {
  await this.page.waitFor(`.AknLoadingIndicator--loading`);
});

Then('I should not see the loading indicator', async function() {
  await this.page.waitFor(`.AknLoadingIndicator:not(.AknLoadingIndicator--loading)`);
});

Then('I should see that we have {int} results', async function(expectedNumberOfResults) {
  await this.page.waitFor(`.AknTitleContainer-title[data-result-count="${expectedNumberOfResults}"]`);
});
