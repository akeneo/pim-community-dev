const path = require('path');
const Sidebar = require('../../decorators/reference-entity/app/sidebar.decorator');
const Header = require('../../decorators/reference-entity/app/header.decorator');
const Records = require('../../decorators/reference-entity/edit/records.decorator');
const {getRequestContract, listenRequest} = require('../../tools');

const {
  decorators: {createElementDecorator}
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
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
  };

  const getElement = createElementDecorator(config);

  const showRecordTab = async function(page) {
    const sidebar = await await getElement(page, 'Sidebar');
    await sidebar.clickOnTab('record');
  };

  When('the user deletes all the reference entity records', async function() {
    await showRecordTab(this.page);

    const requestContract = getRequestContract('Record/DeleteAll/ok.json');
    await listenRequest(this.page, requestContract);

    this.page.once('dialog', async dialog => {
      await dialog.accept();
    });

    const header = await await getElement(this.page, 'Header');
    header.clickOnDeleteButton();
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
};
