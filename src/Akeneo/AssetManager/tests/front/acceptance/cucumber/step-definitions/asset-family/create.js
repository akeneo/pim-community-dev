const Header = require('../../decorators/asset-family/app/header.decorator');
const Modal = require('../../decorators/create/modal.decorator');
const Grid = require('../../decorators/asset-family/index/grid.decorator');
const {getRequestContract, listenRequest} = require('../../tools');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {answerJson, convertItemTable},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Header: {
      selector: '.AknTitleContainer',
      decorator: Header,
    },
    Modal: {
      selector: '.modal',
      decorator: Modal,
    },
    Grid: {
      selector: '.AknGridContainer',
      decorator: Grid,
    },
  };

  const getElement = createElementDecorator(config);

  const saveAssetFamily = async function(page) {
    const requestContract = getRequestContract('AssetFamily/Create/ok.json');

    return await listenRequest(page, requestContract);
  };

  const listAssetFamilyUpdated = async function(page, identifier, labels) {
    page.on('request', request => {
      if ('http://pim.com/rest/asset_manager' === request.url()) {
        answerJson(request, {
          items: [
            {
              identifier: identifier,
              labels: labels,
            },
          ],
          matches_count: 1000,
        });
      }
    });
  };

  const validationMessageShown = async function(page, message) {
    page.on('request', request => {
      if ('http://pim.com/rest/asset_manager' === request.url() && 'POST' === request.method()) {
        answerJson(
          request,
          [
            {
              messageTemplate: 'pim_asset_manager.asset_family.validation.code.pattern',
              parameters: {'{{ value }}': '\u0022invalid/identifier\u0022'},
              plural: null,
              message: message,
              root: {identifier: 'invalid/identifier', labels: []},
              propertyPath: 'code',
              invalidValue: 'invalid/identifier',
              constraint: {defaultOption: null, requiredOptions: [], targets: 'property', payload: null},
              cause: null,
              code: null,
            },
          ],
          400
        );
      }
    });
  };

  When('the user creates an asset family {string} with:', async function(identifier, updates) {
    const assetFamily = convertItemTable(updates)[0];

    await this.page.evaluate(async () => {
      const Controller = require('pim/controller/asset-family/list');
      const controller = new Controller();
      controller.renderRoute();
      await document.getElementById('app').appendChild(controller.el);
    });

    const header = await await getElement(this.page, 'Header');
    await header.clickOnCreateButton();

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_asset_manager.asset_family.create.input.code', identifier);
    if (assetFamily.labels !== undefined && assetFamily.labels.en_US !== undefined) {
      await modal.fillField('pim_asset_manager.asset_family.create.input.label', assetFamily.labels.en_US);
    }
  });

  When('the user saves the asset family', async function() {
    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  Then('there is an asset family {string} with:', async function(identifier, updates) {
    const assetFamily = convertItemTable(updates)[0];

    listAssetFamilyUpdated(this.page, identifier, assetFamily.labels);

    const grid = await await getElement(this.page, 'Grid');
    await grid.hasRow(identifier);

    if (assetFamily.labels !== undefined && assetFamily.labels.en_US !== undefined) {
      const label = await grid.getAssetFamilyLabel(assetFamily.identifier);
      assert.strictEqual(label, assetFamily.labels.en_US);
    }
  });

  Then('The validation error will be {string}', async function(expectedMessage) {
    await validationMessageShown(this.page, expectedMessage);
  });

  Then('the asset family will be saved', async function() {
    await saveAssetFamily(this.page);
  });

  Then('a validation message is displayed {string}', async function(expectedMessage) {
    const modal = await await getElement(this.page, 'Modal');
    const actualMesssage = await modal.getValidationMessageForCode();
    assert.strictEqual(expectedMessage, actualMesssage);
  });

  Then('the user should not be able to create an asset family', async function() {
    const header = await await getElement(this.page, 'Header');
    assert.strictEqual(false, await header.isCreateButtonVisible());
  });
};
