const {Given, Then} = require('cucumber');
const assert = require('assert');
const {createProductWithLabels} = require('../fixtures');
const {answerJson} = require('../tools');

Given(/^a product grid$/, async function() {
  await this.page.evaluate(async () => {
    const bridge = require('pim/product/grid/bridge').default;

    await bridge(document.getElementById('app'));
  });

  await this.page.waitFor('.AknGridContainer');
});

Given('the following product labels:', async function(products) {
  const responseProducts = products.hashes().map(product => {
    const {identifier, ...labels} = product;

    return createProductWithLabels(identifier, labels);
  });

  this.page.on('request', interceptedRequest => {
    if (interceptedRequest.url().includes('/enrich/product/rest/grid/')) {
      answerJson(interceptedRequest, {items: responseProducts, total: responseProducts.length});
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
