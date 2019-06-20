const AssetFamilyBuilder = require('../../../../common/builder/asset-family.js');
const Grid = require('../../decorators/asset-family/index/grid.decorator');
const path = require('path');
const {getRequestContract, listenRequest} = require('../../tools');

const {
  decorators: {createElementDecorator},
  tools: {answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {Given, Then, When} = cucumber;
  const assert = require('assert');

  const config = {
    Grid: {
      selector: '.AknGridContainer',
      decorator: Grid,
    },
  };

  const getElement = createElementDecorator(config);

  const givenAssetFamilies = function(assetFamilies) {
    const assetFamilyResponse = assetFamilies.hashes().map(function(assetFamily) {
      const assetFamilyBuilder = new AssetFamilyBuilder();

      if (undefined !== assetFamily.identifier) {
        assetFamilyBuilder.withIdentifier(assetFamily.identifier);
      }
      if (undefined !== assetFamily.labels) {
        assetFamilyBuilder.withLabels(JSON.parse(assetFamily.labels));
      }
      if (undefined !== assetFamily.image) {
        assetFamilyBuilder.withImage(JSON.parse(assetFamily.image));
      } else {
        assetFamilyBuilder.withImage(null);
      }
      assetFamilyBuilder.withAttributes([]);
      assetFamilyBuilder.withPermission(assetFamily.permission);

      return assetFamilyBuilder.build();
    });

    assetFamilyResponse.forEach(assetFamily => {
      const answerAssetFamilyRequest = request => {
        if (
          `http://pim.com/rest/asset_manager/${assetFamily.identifier}` === request.url() &&
          'GET' === request.method()
        ) {
          this.page.removeListener('request', answerAssetFamilyRequest);
          answerJson(request, assetFamily);
        }
      };
      this.page.on('request', answerAssetFamilyRequest);
    });

    this.page.on('request', request => {
      if ('http://pim.com/rest/asset_manager' === request.url()) {
        answerJson(request, {items: assetFamilyResponse, matches_count: 1000});
      }
    });
  };

  const givenValidAssetFamily = async function() {
    const requestContract = getRequestContract('AssetFamily/AssetFamilyDetails/ok.json');

    await listenRequest(this.page, requestContract);
  };

  const givenValidBrandAssetFamily = async function() {
    const requestContract = getRequestContract('AssetFamily/AssetFamilyDetails/brand_ok.json');

    await listenRequest(this.page, requestContract);
  };

  Given('the following asset families to list:', givenAssetFamilies);
  Given('the following asset families to show:', givenAssetFamilies);
  Given('a valid asset family', givenValidAssetFamily);
  Given('a valid brand asset family', givenValidBrandAssetFamily);
  When('the user asks for the asset family list', async function() {
    await this.page.evaluate(async () => {
      const Controller = require('pim/controller/asset-family/list');
      const controller = new Controller();
      controller.renderRoute();
      await document.getElementById('app').appendChild(controller.el);
    });

    const grid = await await getElement(this.page, 'Grid');
    const isLoaded = await grid.isLoaded();

    assert.equal(isLoaded, true);
  });

  Then('the user gets a selection of {int} items out of {int} items in total', async function(count, total) {
    const grid = await await getElement(this.page, 'Grid');
    const rows = await grid.getRowsAfterLoading();
    assert.equal(rows.length, count);

    const title = await grid.getTitle();
    assert.equal(title.trim(), `${total} Asset Famil${total > 1 ? 'ies' : 'y'}`);
  });

  Then('the user gets an asset family {string}', async function(identifier) {
    const grid = await await getElement(this.page, 'Grid');
    await grid.hasRow(identifier);
  });

  Then('there is no asset family', async function() {
    const grid = await await getElement(this.page, 'Grid');
    const rows = await grid.getRows();
    assert.equal(rows.length, 0);
  });

  Then('the user asks for the next asset families', async function() {
    this.page.evaluate(() => {
      window.scrollBy(0, window.innerHeight);
    });
  });
};
