const path = require('path');
const Sidebar = require('../../decorators/reference-entity/app/sidebar.decorator');
const Header = require('../../decorators/reference-entity/app/header.decorator');
const Records = require('../../decorators/reference-entity/edit/records.decorator');
const Modal = require('../../decorators/delete/modal.decorator');
const {getRequestContract, listenRequest} = require('../../tools');

const {
  decorators: {createElementDecorator},
  tools: {answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {Given, When, Then} = cucumber;
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
    Records: {
      selector: '.AknDefault-mainContent',
      decorator: Records,
    },
    Modal: {
      selector: '.AknFullPage--modal',
      decorator: Modal,
    },
  };

  const getElement = createElementDecorator(config);

  const showRecordTab = async function(page) {
    const sidebar = await await getElement(page, 'Sidebar');
    await sidebar.clickOnTab('record');
  };

  Given('the following records for the reference entity {string}:', async function(referenceEntityIdentifier, records) {
    const recordsSaved = records.hashes().map(normalizedRecord => {
      return {
        identifier: normalizedRecord.identifier,
        reference_entity_identifier: referenceEntityIdentifier,
        code: normalizedRecord.code,
        labels: JSON.parse(normalizedRecord.labels),
      };
    });

    this.page.on('request', request => {
      if (
        `http://pim.com/rest/reference_entity/${referenceEntityIdentifier}/record` === request.url() &&
        'GET' === request.method()
      ) {
        answerJson(request, {items: recordsSaved, total: recordsSaved.length});
      }
    });
  });

  When('the user deletes all the reference entity records', async function() {
    await showRecordTab(this.page);

    const requestContract = getRequestContract('Record/DeleteAll/ok.json');
    await listenRequest(this.page, requestContract);

    const header = await await getElement(this.page, 'Header');
    header.clickOnDeleteButton();

    const modalPage = await await getElement(this.page, 'Modal');
    await modalPage.confirmDeletion();
  });

  When('the user cannot delete all the reference entity records', async function() {
    await showRecordTab(this.page);

    const requestContract = getRequestContract('Record/DeleteAll/error.json');
    await listenRequest(this.page, requestContract);

    this.page.once('dialog', async dialog => {
      await dialog.accept();
    });

    const header = await await getElement(this.page, 'Header');
    header.clickOnDeleteButton();

    const modalPage = await await getElement(this.page, 'Modal');
    await modalPage.confirmDeletion();
  });

  Then('the user should see the successfull deletion notification', async function() {
    const recordsPage = await await getElement(this.page, 'Records');
    const hasSuccessNotification = await recordsPage.hasSuccessNotification();

    assert.strictEqual(hasSuccessNotification, true);
  });

  Then('the user should see the failed deletion notification', async function() {
    const recordsPage = await await getElement(this.page, 'Records');
    const hasSuccessNotification = await recordsPage.hasErrorNotification();

    assert.strictEqual(hasSuccessNotification, true);
  });

  Then('the user should not see the delete all button', async function() {
    await showRecordTab(this.page);

    const header = await await getElement(this.page, 'Header');
    const isDeleteButtonVisible = await header.isDeleteButtonVisible();

    assert.strictEqual(isDeleteButtonVisible, false);
  });

  Then('the list of records should be:', async function(expectedRecords) {
    await showRecordTab(this.page);

    const recordList = await await getElement(this.page, 'Records');
    const isValid = await expectedRecords.hashes().reduce(async (isValid, expectedRecord) => {
      return (await isValid) && (await recordList.hasRecord(expectedRecord.identifier));
    }, true);
    assert.strictEqual(isValid, true);
  });

  Then('the list of records should be empty', async function() {
    await showRecordTab(this.page);

    const records = await await getElement(this.page, 'Records');
    const isEmpty = await records.isEmpty();

    assert.strictEqual(isEmpty, true);
  });

  Then('the list of records should not be empty', async function() {
    await showRecordTab(this.page);

    const records = await await getElement(this.page, 'Records');
    const isEmpty = await records.isEmpty();

    assert.strictEqual(isEmpty, false);
  });
};
