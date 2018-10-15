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
      selector: '.AknFullPage--modal',
      decorator: Modal,
    },
  };

  const getElement = createElementDecorator(config);

  Given('a valid record', async function() {
    const requestContract = getRequestContract('Record/RecordDetails/ok.json');
    currentRequestContract = requestContract;

    return await listenRequest(this.page, requestContract);
  });
  Given('an invalid record', async function() {
    const requestContract = getRequestContract('Record/RecordDetails/not_found.json');
    currentRequestContract = requestContract;

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
    for (let locale in record.labels) {
      const label = record.labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);
      const labelValue = await await enrich.getLabel();
      assert.strictEqual(labelValue, label);
    }
  });

  When('the user saves the valid record', async function() {
    await answerLocaleList.apply(this);
    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.referenceEntityIdentifier,
    ]);

    const requestContract = getRequestContract('Record/Edit/details_ok.json');

    await listenRequest(this.page, requestContract);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('pim_reference_entity.record.enrich.label', 'Michel Starck');
    await editPage.save();
  });

  When('the user saves the valid record with a simple text value', async function() {
    await answerLocaleList.apply(this);
    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.referenceEntityIdentifier,
    ]);

    const requestContract = getRequestContract('Record/Edit/text_value_ok.json');

    await listenRequest(this.page, requestContract);
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('name_designer_fingerprint', 'Starck');
    await editPage.save();
  });

  When('the user updates the valid record with an image value', async function() {
    await answerLocaleList.apply(this);
    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.referenceEntityIdentifier,
    ]);

    const requestContract = getRequestContract('Record/Edit/image_value_ok.json');

    await answerMedia.apply(this);

    await listenRequest(this.page, requestContract);
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillUploadField('portrait_designer_fingerprint', './../../../../common/ressource/philippe_starck.png');
    await editPage.save();
  });

  When('the user saves the valid record with an invalid simple text value', async function() {
    await answerLocaleList.apply(this);
    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.referenceEntityIdentifier,
    ]);

    const requestContract = getRequestContract('Record/Edit/invalid_text_value.json');

    await listenRequest(this.page, requestContract);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillField('website_designer_fingerprint', 'hello world');
    await editPage.save();
  });

  When('the user saves the valid record with an invalid image value', async function() {
    await answerMedia.apply(this);
    await answerLocaleList.apply(this);
    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.referenceEntityIdentifier,
    ]);

    const requestContract = getRequestContract('Record/Edit/image_value_ok.json');

    await listenRequest(this.page, requestContract);
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.fillUploadField('portrait_designer_fingerprint', './../../../../common/ressource/invalid_image.png');
    await editPage.save();
  });

  When('the user deletes the record', async function() {
    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.referenceEntityIdentifier,
    ]);

    const requestContract = getRequestContract('Record/Delete/ok.json');

    await listenRequest(this.page, requestContract);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    await enrich.clickOnDeleteButton();

    const modalPage = await await getElement(this.page, 'Modal');
    await modalPage.confirmDeletion();
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

  Then('the user shouldn\'t see the delete button', async function() {
    await askForRecord.apply(this, [
      currentRequestContract.request.query.recordCode,
      currentRequestContract.request.query.referenceEntityIdentifier,
    ]);

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const hasDeleteButton = await enrich.hasDeleteButton();

    assert.strictEqual(hasDeleteButton, false);
  });
};
