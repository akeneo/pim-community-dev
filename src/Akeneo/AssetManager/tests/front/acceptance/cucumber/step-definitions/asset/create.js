const Header = require('../../decorators/asset-family/app/header.decorator');
const Sidebar = require('../../decorators/asset-family/app/sidebar.decorator');
const Modal = require('../../decorators/create/modal.decorator');
const Assets = require('../../decorators/asset-family/edit/assets.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {answerJson, convertItemTable},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {When, Then, Given} = cucumber;
  const assert = require('assert');

  const config = {
    Sidebar: {
      selector: '.AknColumn',
      decorator: Sidebar,
    },
    Header: {
      selector: '.AknTitleContainer',
      decorator: Header,
    },
    Modal: {
      selector: '.modal',
      decorator: Modal,
    },
    Assets: {
      selector: '.AknDefault-mainContent',
      decorator: Assets,
    },
  };

  const getElement = createElementDecorator(config);

  const saveAsset = async function(page) {
    page.on('request', request => {
      if ('http://pim.com/rest/asset_manager/designer/asset' === request.url() && 'POST' === request.method()) {
        answerJson(request, {}, 204);
      }
    });
  };

  const listAssetUpdated = async function(page, assetFamilyIdentifier, identifier, code, labels) {
    page.on('request', request => {
      if ('http://pim.com/rest/asset_manager/designer/asset' === request.url() && 'GET' === request.method()) {
        answerJson(request, {
          items: [
            {
              identifier: identifier,
              asset_family_identifier: assetFamilyIdentifier,
              code: code,
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
      if ('http://pim.com/rest/asset_manager/designer/asset' === request.url() && 'POST' === request.method()) {
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

  const getAssetIdentifier = function(assetFamilyIdentifier, code) {
    return `${assetFamilyIdentifier}_${code}_123456`;
  };

  When('the user creates a asset of {string} with:', async function(assetFamilyIdentifier, updates) {
    const asset = convertItemTable(updates)[0];

    const sidebar = await await getElement(this.page, 'Sidebar');
    await sidebar.clickOnTab('asset');

    const header = await await getElement(this.page, 'Header');
    await header.clickOnCreateButton();

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_asset_manager.asset.create.input.code', asset.code);
    if (asset.labels !== undefined && asset.labels.en_US !== undefined) {
      await modal.fillField('pim_asset_manager.asset.create.input.label', asset.labels.en_US);
    }
  });

  Given('the user toggles the sequantial creation', async function() {
    const modal = await await getElement(this.page, 'Modal');
    await modal.toggleCreateAnother();
  });

  Then('the asset creation form should be displayed', async function() {
    await this.page.waitFor(1000);
    await this.page.waitFor('.modal .AknFullPage-content .AknFieldContainer');
  });

  When('the user saves the asset', async function() {
    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  Then('there is a asset of {string} with:', async function(assetFamilyIdentifier, updates) {
    const asset = convertItemTable(updates)[0];
    const assetIdentifier = getAssetIdentifier(assetFamilyIdentifier, asset.code);

    await listAssetUpdated(this.page, assetFamilyIdentifier, assetIdentifier, asset.code, asset.labels);

    const assets = await await getElement(this.page, 'Assets');
    await assets.hasAsset(assetIdentifier);

    if (asset.labels !== undefined && asset.labels.en_US !== undefined) {
      const label = await assets.getAssetLabel(assetIdentifier);
      assert.strictEqual(label, asset.labels.en_US);
    }
  });

  Then('the asset validation error will be {string}', async function(expectedMessage) {
    await validationMessageShown(this.page, expectedMessage);
  });

  Then('the asset will be saved', async function() {
    await saveAsset(this.page);
  });

  Then('the user cannot create a asset', async function() {
    const sidebar = await await getElement(this.page, 'Sidebar');
    await sidebar.clickOnTab('asset');

    const header = await await getElement(this.page, 'Header');
    const isCreateButtonVisible = await header.isCreateButtonVisible();

    assert.strictEqual(isCreateButtonVisible, false);
  });
};
