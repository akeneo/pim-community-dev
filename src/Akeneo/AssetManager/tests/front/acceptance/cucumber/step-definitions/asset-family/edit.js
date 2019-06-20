const Edit = require('../../decorators/asset-family/edit.decorator');
const Header = require('../../decorators/asset-family/app/header.decorator');
const Modal = require('../../decorators/delete/modal.decorator');
const path = require('path');
const {askForAssetFamily} = require('../../tools');

const {
  decorators: {createElementDecorator},
  tools: {convertDataTable, convertItemTable, answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Header: {
      selector: '.AknTitleContainer',
      decorator: Header,
    },
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
    },
    Modal: {
      selector: '.modal',
      decorator: Modal,
    },
  };

  const getElement = createElementDecorator(config);

  const changeAssetFamily = async function(editPage, identifier, updates) {
    const properties = await editPage.getProperties();

    const labels = convertDataTable(updates).labels;

    for (const locale in labels) {
      const label = labels[locale];
      const localeSwitcher = await editPage.getLocaleSwitcher();
      await localeSwitcher.switchLocale(locale);
      await properties.setLabel(label);
    }
  };

  const savedAssetFamilyWillBe = function(page, identifier, updates) {
    page.on('request', request => {
      if (`http://pim.com/rest/asset_manager/${identifier}` === request.url() && 'POST' === request.method()) {
        answerJson(request, {}, 204);
      }

      if (`http://pim.com/rest/asset_manager/${identifier}` === request.url() && 'GET' === request.method()) {
        answerJson(request, {...convertItemTable(updates)[0], asset_count: 123, attributes: []}, 200);
      }
    });
  };

  When('the user asks for the asset family {string}', askForAssetFamily);

  When('the user gets the asset family {string} with label {string}', async function(
    expectedIdentifier,
    expectedLabel
  ) {
    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    const identifierValue = await properties.getIdentifier();
    assert.strictEqual(identifierValue, expectedIdentifier);

    const labelValue = await properties.getLabel();
    assert.strictEqual(labelValue, expectedLabel);
  });

  When('the user updates the asset family {string} with:', async function(identifier, updates) {
    await askForAssetFamily.apply(this, [identifier]);

    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    await properties.isLoaded();
    await changeAssetFamily(editPage, identifier, updates);
    await savedAssetFamilyWillBe(this.page, identifier, updates);
    await editPage.save();
  });

  When('the user changes the asset family {string} with:', async function(identifier, updates) {
    await askForAssetFamily.apply(this, [identifier]);
    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    await properties.isLoaded();

    await changeAssetFamily.apply(this, [editPage, identifier, updates]);
  });

  Then('the asset family {string} should be:', async function(identifier, updates) {
    const assetFamily = convertItemTable(updates)[0];

    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    const identifierValue = await properties.getIdentifier();
    assert.strictEqual(identifierValue, assetFamily.identifier);

    for (const locale in assetFamily.labels) {
      const label = assetFamily.labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);
      const labelValue = await properties.getLabel();
      assert.strictEqual(labelValue, label);
    }
  });

  Then('the saved asset family {string} will be:', async function(identifier, updates) {
    await savedAssetFamilyWillBe(this.page, identifier, updates);
  });

  Then('the user saves the changes', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    await editPage.save();
  });

  Then('the user should see the saved notification', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasSuccessNotification = await editPage.hasSuccessNotification();

    assert.strictEqual(hasSuccessNotification, true);
  });

  Then('the asset family {string} save will fail', function(identifier) {
    this.page.on('request', request => {
      if (`http://pim.com/rest/asset_manager/${identifier}` === request.url() && 'POST' === request.method()) {
        request.respond({
          status: 500,
          contentType: 'text/plain',
          body: 'Internal Error',
        });
      }
    });
  });

  Then('the user should see the saved notification error', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasErrorNotification = await editPage.hasErrorNotification();

    assert.strictEqual(hasErrorNotification, true);
  });

  When('the user deletes the asset family {string}', async function(identifier) {
    const editPage = await await getElement(this.page, 'Edit');
    await editPage.getProperties();
    const header = await await getElement(this.page, 'Header');

    this.page.once('request', request => {
      if (`http://pim.com/rest/asset_manager/${identifier}` === request.url() && 'DELETE' === request.method()) {
        request.respond({
          status: 204,
          contentType: 'application/json',
          body: null,
        });
      }
    });

    await header.clickOnDeleteButton();

    const modalPage = await await getElement(this.page, 'Modal');
    await modalPage.confirmDeletion();
  });

  When('the user fails to delete the asset family {string}', async function(identifier) {
    const editPage = await await getElement(this.page, 'Edit');
    await editPage.getProperties();
    const header = await await getElement(this.page, 'Header');
    const response = JSON.stringify([
      {
        messageTemplate: 'pim_asset_manager.asset_family.validation.assets.should_have_no_asset',
        parameters: {'%asset_family_identifier%': []},
        plural: null,
        message: 'You cannot delete this entity because assets exist for this entity',
        root: {identifier: `${identifier}`},
        propertyPath: '',
        invalidValue: {identifier: `${identifier}`},
        constraint: {targets: 'class', defaultOption: null, requiredOptions: [], payload: null},
        cause: null,
        code: null,
      },
    ]);

    this.page.once('request', request => {
      if (`http://pim.com/rest/asset_manager/${identifier}` === request.url() && 'DELETE' === request.method()) {
        request.respond({
          status: 400,
          contentType: 'application/json',
          body: response,
        });
      }
    });

    await header.clickOnDeleteButton();

    const modalPage = await await getElement(this.page, 'Modal');
    await modalPage.confirmDeletion();
  });

  When('the user refuses to delete the current asset family', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    await editPage.getProperties();
    const header = await await getElement(this.page, 'Header');

    const dismissDelete = async dialog => {
      this.page.removeListener('dialog', dismissDelete);
      await dialog.dismiss();
    };
    this.page.on('dialog', dismissDelete);

    await header.clickOnDeleteButton();
  });

  Then('the user should see the deleted notification', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasSuccessNotification = await editPage.hasSuccessNotification();

    assert.strictEqual(hasSuccessNotification, true);
  });

  Then('the user should see the delete notification error', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasErrorNotification = await editPage.hasErrorNotification();

    assert.strictEqual(hasErrorNotification, true);
  });

  Then('the user should not be notified that deletion has been made', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasNoNotification = await editPage.hasNoNotification();

    assert.strictEqual(hasNoNotification, true);
  });

  Then('the user should not see the deletion button', async function() {
    const header = await await getElement(this.page, 'Header');
    const isDeleteButtonVisible = await header.isDeleteButtonVisible();

    assert.strictEqual(isDeleteButtonVisible, false);
  });

  Then('the label of the asset family {string} should be read only', async function(identifier) {
    await askForAssetFamily.apply(this, [identifier]);
    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    await properties.isLoaded();

    await properties.labelIsReadOnly();
  });

  Then('the save button should not be displayed', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    await editPage.hasNoSaveButton();
  });
};
