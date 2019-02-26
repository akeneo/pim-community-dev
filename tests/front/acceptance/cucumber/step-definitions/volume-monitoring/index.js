module.exports = async function(cucumber) {
  const {Given, Then, When, Before} = cucumber;
  const assert = require('assert');
  const path = require('path');
  const {
    decorators: {createElementDecorator, Report},
    tools: {renderView},
  } = require('../../test-helpers.js');

  const config = {
    'Catalog volume report': {
      selector: '.AknDefault-mainContent',
      decorator: Report,
    },
  };

  let data = {
    count_asset_categories: {
      value: 5,
      has_warning: false,
      type: 'count',
    },
  };

  getElement = createElementDecorator(config);

  Given('a catalog with {int} asset categories', async function(int) {
    this.page.on('request', request => {
      if (request.url().includes('/security')) {
        request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify({})}`
        })
      }
    })

    await renderView(this.page, 'pim-catalog-volume-index', data);
    assert(int);
  });

  Then('the report returns that the number of asset categories is {int}', async function(int) {
    const report = await await getElement(this.page, 'Catalog volume report');
    const volume = await report.getVolumeByType('count_asset_categories');
    const value = await volume.getValue();
    assert.equal(value, int);
  });
};
