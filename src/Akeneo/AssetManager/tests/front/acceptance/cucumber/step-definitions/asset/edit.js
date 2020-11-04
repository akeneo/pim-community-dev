const Edit = require('../../decorators/asset/edit.decorator');
const Product = require('../../decorators/asset/product.decorator');
const {getRequestContract, listenRequest, answerLocaleList, answerChannelList} = require('../../tools');
const Header = require('../../decorators/asset-family/app/header.decorator');
const Sidebar = require('../../decorators/asset-family/app/sidebar.decorator');
const Modal = require('../../decorators/delete/modal.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {convertItemTable, answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

let currentRequestContract = {};

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
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
    },
    Product: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Product,
    },
    Modal: {
      selector: '.modal',
      decorator: Modal,
    },
  };

  const getElement = createElementDecorator(config);

  Given('a valid asset', async function() {
    const requestContract = getRequestContract('Asset/AssetDetails/ok.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('a valid asset with an option attribute', async function() {
    await answerLocaleList.apply(this);
    const requestContract = getRequestContract('Asset/AssetDetails/ok/option.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('a valid asset with an option collection attribute', async function() {
    await answerLocaleList.apply(this);
    const requestContract = getRequestContract('Asset/AssetDetails/ok/option_collection.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('the user asks for the list of linked product', async function() {
    await answerLocaleList.apply(this);
    const productRequestContract = getRequestContract('Asset/Product/ok.json');

    await listenRequest(this.page, productRequestContract);
    const attributeRequestContract = getRequestContract('Asset/Product/Attribute/ok.json');

    await listenRequest(this.page, attributeRequestContract);

    return await loadEditAsset.apply(this, ['Asset/Edit/details_ok.json']);
  });

  Given('the user asks for the list of linked product without any asset family attribute', async function() {
    await answerLocaleList.apply(this);
    const productRequestContract = getRequestContract('Asset/Product/ok.json');

    await listenRequest(this.page, productRequestContract);
    const attributeRequestContract = getRequestContract('Asset/Product/Attribute/empty.json');

    await listenRequest(this.page, attributeRequestContract);

    return await loadEditAsset.apply(this, ['Asset/Edit/details_ok.json']);
  });

  Given('the user asks for the list of linked product without any linked product', async function() {
    await answerLocaleList.apply(this);
    const productRequestContract = getRequestContract('Asset/Product/empty.json');

    await listenRequest(this.page, productRequestContract);
    const attributeRequestContract = getRequestContract('Asset/Product/Attribute/ok.json');

    await listenRequest(this.page, attributeRequestContract);

    return await loadEditAsset.apply(this, ['Asset/Edit/details_ok.json']);
  });

  Given('the user should see the list of products linked to the asset', async function() {
    const sidebar = await getElement(this.page, 'Sidebar');
    await sidebar.clickOnTab('product');
    const products = await getElement(this.page, 'Product');
    const product1 = await products.productExists('1111111304');
    const product2 = await products.productExists('model-braided-hat');

    assert.strictEqual(product1, true);
    assert.strictEqual(product2, true);
  });

  Given('the user should not see any linked product', async function() {
    const sidebar = await getElement(this.page, 'Sidebar');
    await sidebar.clickOnTab('product');

    const products = await getElement(this.page, 'Product');
    const noLinkedProduct = await products.noLinkedProduct();
    assert.strictEqual(noLinkedProduct, true);
  });

  Given('the user should not see any linked product attribute', async function() {
    const sidebar = await getElement(this.page, 'Sidebar');
    await sidebar.clickOnTab('product');

    const products = await getElement(this.page, 'Product');
    const noLinkedAttribute = await products.noLinkedAttribute();
    assert.strictEqual(noLinkedAttribute, true);
  });

  Given('an invalid asset', async function() {
    await answerLocaleList.apply(this);
    const requestContract = getRequestContract('Asset/AssetDetails/not_found.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('a valid asset with an asset family single link attribute', async function() {
    await answerLocaleList.apply(this);
    const requestContract = getRequestContract('Asset/AssetDetails/ok/asset.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('a valid asset with an asset collection attribute', async function() {
    await answerLocaleList.apply(this);
    const requestContract = getRequestContract('Asset/AssetDetails/ok/asset_collection.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('the user has the locale permission to edit the asset', async function() {
    const requestContract = getRequestContract('Permission/Locale/ok.json');

    return await listenRequest(this.page, requestContract);
  });

  const answerMedia = async function() {
    await this.page.on('request', request => {
      if ('http://pim.com/rest/media/' === request.url() && 'POST' === request.method()) {
        answerJson(
          request,
          {
            originalFilename: 'philou.png',
            filePath: '/a/b/c/philou.png',
          },
          200
        );
      }
    });
  };

  const askForAsset = async function(assetCode, assetFamilyIdentifier) {
    await this.page.evaluate(
      async (assetFamilyIdentifier, assetCode) => {
        const Controller = require('pim/controller/asset/edit');
        const controller = new Controller();
        controller.renderRoute({params: {assetFamilyIdentifier, assetCode, tab: 'enrich'}});
        await document.getElementById('app').appendChild(controller.el);
      },
      assetFamilyIdentifier,
      assetCode
    );
    await this.page.waitFor('.AknDefault-mainContent[data-tab="enrich"] .content');
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isLoaded = await enrich.isLoaded();
    assert.strictEqual(isLoaded, true);
  };

  const loadEditAsset = async function(requestContractPath) {
    await answerChannelList.apply(this);
    await askForAsset.apply(this, [
      currentRequestContract.request.query.assetCode,
      currentRequestContract.request.query.assetFamilyIdentifier,
    ]);

    const requestContract = getRequestContract(requestContractPath);

    await listenRequest(this.page, requestContract);
  };

  When('the user ask for the asset', async function() {
    await answerLocaleList.apply(this);
    await answerChannelList.apply(this);

    await askForAsset.apply(this, [
      currentRequestContract.request.query.assetCode,
      currentRequestContract.request.query.assetFamilyIdentifier,
    ]);
  });

  Then('the asset should be:', async function(updates) {
    const asset = convertItemTable(updates)[0];
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();

    await (await editPage.getChannelSwitcher()).switchChannel('mobile');
    for (let locale in asset.labels) {
      const label = asset.labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);
      const labelValue = await await enrich.getLabel();
      assert.strictEqual(labelValue, label);
    }
  });

  When('the user saves the valid asset', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/details_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('pim_asset_manager.asset.enrich.name', 'Starck');
    await editPage.save();
  });

  When('the user saves the valid asset with a simple text value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/text_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('pim_asset_manager.asset.enrich.name', 'Starck');
    await editPage.save();
  });

  When('the user saves the valid asset with a number value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/number_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('pim_asset_manager.asset.enrich.age', '39');
    await editPage.save();
  });

  When('the user saves the valid asset with a number out of range', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/invalid_number_out_of_range.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('pim_asset_manager.asset.enrich.age', '-39');
    await editPage.save();
  });

  When('the user updates the valid asset with an image value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/image_value_ok.json']);
    await answerMedia.apply(this);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillUploadField(
      'pim_asset_manager.asset.enrich.portrait',
      './../../../../common/ressource/philippe_starck.png'
    );
    await editPage.save();
  });

  When('the user saves the valid asset with a simple option value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/option_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillSelectField('pim_asset_manager.asset.enrich.option', 'red');
    await editPage.save();
  });

  When('the user saves the valid asset with a multiple option value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/option_collection_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillSelectField('pim_asset_manager.asset.enrich.option_collection', 'red');
    await editPage.save();
  });

  When('the user saves the valid asset with an invalid simple text value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/invalid_text_value.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('pim_asset_manager.asset.enrich.website', 'hello world');
    await editPage.save();
  });

  When('the user saves the valid asset with an invalid simple option value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/invalid_option_value.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillSelectField('pim_asset_manager.asset.enrich.option', 'red');
    await editPage.save();
  });

  When('the user saves the valid asset with an invalid multiple option value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/invalid_option_collection_value.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillSelectField('pim_asset_manager.asset.enrich.option_collection', 'red');
    await editPage.save();
  });

  When('the user saves the valid asset with an invalid image value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/image_value_ok.json']);
    await answerMedia.apply(this);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillUploadField(
      'pim_asset_manager.asset.enrich.portrait',
      './../../../../common/ressource/invalid_image.png'
    );
    await editPage.save();
  });

  When('the user deletes the asset', async function() {
    await loadEditAsset.apply(this, ['Asset/Delete/ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.clickOnDeleteButton();

    const modalPage = await await getElement(this.page, 'Modal');
    await modalPage.confirmDeletion();
  });

  When('the user saves the valid asset with a single asset linked', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/asset_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillAssetSelectField('pim_asset_manager.asset.enrich.linked_brand', 'ikea');
    await editPage.save();
  });

  When('the user saves the valid asset with a multiple asset linked', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/asset_collection_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillAssetSelectField('pim_asset_manager.asset.enrich.linked_cities', 'paris,lisbonne,moscou');
    await editPage.save();
  });

  Then('the user should see a success message on the edit page', async function() {
    const edit = await await getElement(this.page, 'Edit');
    const hasSuccessNotification = await edit.hasSuccessNotification();
    assert.strictEqual(hasSuccessNotification, true);
  });

  Then('the user should see the validation error on the edit page : {string}', async function(expectedError) {
    const edit = await await getElement(this.page, 'Edit');
    const error = await edit.getValidationMessageForCode();
    assert.strictEqual(error, expectedError);
  });

  Then('the user should not see the delete button', async function() {
    await answerChannelList.apply(this);
    await askForAsset.apply(this, [
      currentRequestContract.request.query.assetCode,
      currentRequestContract.request.query.assetFamilyIdentifier,
    ]);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const hasDeleteButton = await enrich.hasDeleteButton();

    assert.strictEqual(hasDeleteButton, false);
  });

  Then('the user should see a completeness bullet point on the required field: {string}', async function(field) {
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isFilled = await enrich.isFilled(field);

    assert.strictEqual(isFilled, false);
  });

  When('the user fill the {string} field with: {string}', async function(fieldCode, value) {
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('pim_asset_manager.asset.enrich.' + fieldCode, value);
  });

  Then('the user should not see a completeness bullet point on the required field: {string}', async function(field) {
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isFilled = await enrich.isFilled(field);

    assert.strictEqual(isFilled, true);
  });

  Then('the user should see the completeness percentage with a value of {string}', async function(value) {
    const editPage = await await getElement(this.page, 'Edit');
    const completenessValue = await editPage.getCompletenessValue();

    assert.strictEqual(completenessValue, 'Complete: ' + value);
  });

  Then('the user cannot save the asset', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/details_ok.json']);

    const header = await await getElement(this.page, 'Header');
    const isCreateButtonVisible = await header.isCreateButtonVisible();

    assert.strictEqual(isCreateButtonVisible, false);
  });

  Then('the user cannot update the simple text value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/text_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledTextField = await enrich.isDisabledTextField('pim_asset_manager.asset.enrich.name');

    assert.strictEqual(isDisabledTextField, true);
  });

  Then('the user cannot update the simple option value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/option_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledSelectField = await enrich.isDisabledSelectField('pim_asset_manager.asset.enrich.option');

    assert.strictEqual(isDisabledSelectField, true);
  });

  Then('the user cannot update the multiple option value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/option_collection_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledSelectField = await enrich.isDisabledSelectField(
      'pim_asset_manager.asset.enrich.option_collection'
    );

    assert.strictEqual(isDisabledSelectField, true);
  });

  Then('the user cannot update the single asset linked value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/asset_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledAssetSelectField = await enrich.isDisabledAssetSelectField(
      'pim_asset_manager.asset.enrich.linked_brand'
    );

    assert.strictEqual(isDisabledAssetSelectField, true);
  });

  Then('the user cannot update the multiple asset linked value', async function() {
    await loadEditAsset.apply(this, ['Asset/Edit/asset_collection_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledAssetSelectField = await enrich.isDisabledAssetSelectField(
      'pim_asset_manager.asset.enrich.linked_cities'
    );

    assert.strictEqual(isDisabledAssetSelectField, true);
  });
};
