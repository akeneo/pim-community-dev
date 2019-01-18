const Edit = require('../../decorators/record/edit.decorator');
const {getRequestContract, listenRequest, answerLocaleList, answerChannelList} = require('../../tools');
const Header = require('../../decorators/reference-entity/app/header.decorator');
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

  Given('a valid record', async function() {
    const requestContract = getRequestContract('Record/RecordDetails/ok.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('a valid record with an option attribute', async function() {
    await answerLocaleList.apply(this);
    const requestContract = getRequestContract('Record/RecordDetails/ok/option.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('a valid record with an option collection attribute', async function() {
    await answerLocaleList.apply(this);
    const requestContract = getRequestContract('Record/RecordDetails/ok/option_collection.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('an invalid record', async function() {
    await answerLocaleList.apply(this);
    const requestContract = getRequestContract('Record/RecordDetails/not_found.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('a valid record with a reference entity single link attribute', async function() {
    await answerLocaleList.apply(this);
    const requestContract = getRequestContract('Record/RecordDetails/ok/record.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('a valid record with a reference entity multiple link attribute', async function() {
    await answerLocaleList.apply(this);
    const requestContract = getRequestContract('Record/RecordDetails/ok/record_collection.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });

  Given('the user has the locale permission to edit the record', async function() {
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

  const askForRecord = async function(recordCode, referenceEntityIdentifier) {
    await this.page.evaluate(
      async (referenceEntityIdentifier, recordCode) => {
        const Controller = require('pim/controller/record/edit');
        const controller = new Controller();
        controller.renderRoute({params: {referenceEntityIdentifier, recordCode, tab: 'enrich'}});
        await document.getElementById('app').appendChild(controller.el);
      },
      referenceEntityIdentifier,
      recordCode
    );
    await this.page.waitFor('.AknDefault-mainContent[data-tab="enrich"] .content');
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isLoaded = await enrich.isLoaded();
    assert.strictEqual(isLoaded, true);
  };

  const loadEditRecord = async function(requestContractPath) {
    await answerChannelList.apply(this);
    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.referenceEntityIdentifier,
    ]);

    const requestContract = getRequestContract(requestContractPath);

    await listenRequest(this.page, requestContract);
  };

  When('the user ask for the record', async function() {
    await answerLocaleList.apply(this);
    await answerChannelList.apply(this);

    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.referenceEntityIdentifier,
    ]);
  });

  Then('the record should be:', async function(updates) {
    const record = convertItemTable(updates)[0];
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();

    await (await editPage.getChannelSwitcher()).switchChannel('mobile');
    for (let locale in record.labels) {
      const label = record.labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);
      const labelValue = await await enrich.getLabel();
      assert.strictEqual(labelValue, label);
    }
  });

  When('the user saves the valid record', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/details_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('pim_reference_entity.record.enrich.name', 'Starck');
    await editPage.save();
  });

  When('the user saves the valid record with a simple text value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/text_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('pim_reference_entity.record.enrich.name', 'Starck');
    await editPage.save();
  });

  When('the user updates the valid record with an image value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/image_value_ok.json']);
    await answerMedia.apply(this);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillUploadField(
      'pim_reference_entity.record.enrich.portrait',
      './../../../../common/ressource/philippe_starck.png'
    );
    await editPage.save();
  });

  When('the user saves the valid record with a simple option value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/option_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillSelectField('pim_reference_entity.record.enrich.option', 'red');
    await editPage.save();
  });

  When('the user saves the valid record with a multiple option value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/option_collection_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillSelectField('pim_reference_entity.record.enrich.option_collection', 'red');
    await editPage.save();
  });

  When('the user saves the valid record with an invalid simple text value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/invalid_text_value.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('pim_reference_entity.record.enrich.website', 'hello world');
    await editPage.save();
  });

  When('the user saves the valid record with an invalid simple option value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/invalid_option_value.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillSelectField('pim_reference_entity.record.enrich.option', 'red');
    await editPage.save();
  });

  When('the user saves the valid record with an invalid multiple option value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/invalid_option_collection_value.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillSelectField('pim_reference_entity.record.enrich.option_collection', 'red');
    await editPage.save();
  });

  When('the user saves the valid record with an invalid image value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/image_value_ok.json']);
    await answerMedia.apply(this);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillUploadField(
      'pim_reference_entity.record.enrich.portrait',
      './../../../../common/ressource/invalid_image.png'
    );
    await editPage.save();
  });

  When('the user deletes the record', async function() {
    await loadEditRecord.apply(this, ['Record/Delete/ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.clickOnDeleteButton();

    const modalPage = await await getElement(this.page, 'Modal');
    await modalPage.confirmDeletion();
  });

  When('the user saves the valid record with a single record linked', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/record_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillRecordSelectField('pim_reference_entity.record.enrich.linked_brand', 'ikea');
    await editPage.save();
  });

  When('the user saves the valid record with a multiple record linked', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/record_collection_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillRecordSelectField('pim_reference_entity.record.enrich.linked_cities', 'paris,lisbonne,moscou');
    await editPage.save();
  });

  Then('the user cannot update the label of a valid record', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/details_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledTextField = await enrich.isDisabledTextField('pim_reference_entity.record.enrich.label');

    assert.strictEqual(isDisabledTextField, true);
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
    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.referenceEntityIdentifier,
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
    await enrich.fillField('pim_reference_entity.record.enrich.' + fieldCode, value);
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

  Then('the user cannot save the record', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/details_ok.json']);

    const header = await await getElement(this.page, 'Header');
    const isCreateButtonVisible = await header.isCreateButtonVisible();

    assert.strictEqual(isCreateButtonVisible, false);
  });

  Then('the user cannot update the simple text value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/text_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledTextField = await enrich.isDisabledTextField('pim_reference_entity.record.enrich.name');

    assert.strictEqual(isDisabledTextField, true);
  });

  Then('the user cannot update the simple option value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/option_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledSelectField = await enrich.isDisabledSelectField('pim_reference_entity.record.enrich.option');

    assert.strictEqual(isDisabledSelectField, true);
  });

  Then('the user cannot update the multiple option value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/option_collection_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledSelectField = await enrich.isDisabledSelectField(
      'pim_reference_entity.record.enrich.option_collection'
    );

    assert.strictEqual(isDisabledSelectField, true);
  });

  Then('the user cannot update the single record linked value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/record_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledRecordSelectField = await enrich.isDisabledRecordSelectField(
      'pim_reference_entity.record.enrich.linked_brand'
    );

    assert.strictEqual(isDisabledRecordSelectField, true);
  });

  Then('the user cannot update the multiple record linked value', async function() {
    await loadEditRecord.apply(this, ['Record/Edit/record_collection_value_ok.json']);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isDisabledRecordSelectField = await enrich.isDisabledRecordSelectField(
      'pim_reference_entity.record.enrich.linked_cities'
    );

    assert.strictEqual(isDisabledRecordSelectField, true);
  });
};
