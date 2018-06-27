const Edit = require('../../decorators/enriched-entity/edit.decorator');
const Breadcrumb = require('../../decorators/enriched-entity/app/breadcrumb.decorator');

const {
  decorators: {createElementDecorator},
  tools: {convertDataTable, convertItemTable, answerJson},
} = require('../../test-helpers.js');

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
    },
    Breadcrumb: {
      selector: '.AknBreadcrumb',
      decorator: Breadcrumb,
    },
  };

  const getElement = createElementDecorator(config);

  const askForEnrichedEntity = async function(identifier) {
    await this.page.evaluate(async identifier => {
      const Controller = require('pim/controller/enriched-entity/edit');
      const controller = new Controller();
      controller.renderRoute({params: {identifier}});
      const element = controller.el;
      await document.getElementById('app').appendChild(controller.el);
    }, identifier);

    await this.page.waitFor('.object-attributes');
    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    const isLoaded = await properties.isLoaded();

    assert.strictEqual(isLoaded, true);
  };

  const changeEnrichedEntity = async function(editPage, identifier, updates) {
    const properties = await editPage.getProperties();

    // To rework when we will be able to switch locale
    const labels = convertDataTable(updates).labels;

    await properties.setLabel(labels.en_US);
  };

  const savedEnrichedEntityWillBe = async function(page, identifier, updates) {
    page.on('request', request => {
      if (`http://pim.com/rest/enriched_entity/${identifier}` === request.url() && 'POST' === request.method()) {
        answerJson(request, convertItemTable(updates)[0]);
      }
    });
  };

  When('the user asks for the enriched entity {string}', askForEnrichedEntity);

  When('the user gets the enriched entity {string} with label {string}', async function(
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

  When('the user updates the enriched entity {string} with:', async function(identifier, updates) {
    await askForEnrichedEntity.apply(this, [identifier]);

    const editPage = await await getElement(this.page, 'Edit');
    await changeEnrichedEntity(editPage, identifier, updates);
    await savedEnrichedEntityWillBe(this.page, identifier, updates);
    await editPage.save();
  });

  When('the user changes the enriched entity {string} with:', async function(identifier, updates) {
    await askForEnrichedEntity.apply(this, [identifier]);

    const editPage = await await getElement(this.page, 'Edit');

    await changeEnrichedEntity.apply(this, [editPage, identifier, updates]);
  });

  When('the user click on a breadcrumb item', async function () {
    const breadcrumb = await await getElement(this.page, 'Breadcrumb');
    await breadcrumb.clickOnItem();
  });

  When('the user goes to {string}', async function (url) {
    await this.page.goto(url);
  });

  Then('the enriched entity {string} should be:', async function(identifier, updates) {
    const enrichedEntity = convertItemTable(updates)[0];

    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    const identifierValue = await properties.getIdentifier();
    assert.strictEqual(identifierValue, enrichedEntity.identifier);

    const labelValue = await properties.getLabel();
    // To rework when we will be able to switch locale
    assert.strictEqual(labelValue, enrichedEntity.labels['en_US']);
  });

  Then('the saved enriched entity {string} will be:', function(identifier, updates) {
    savedEnrichedEntityWillBe(this.page, identifier, updates);
  });

  Then('the user saves the changes', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    await editPage.save();
  });

  Then('the user should be notified that modification have been made', async function () {
    const editPage = await await getElement(this.page, 'Edit');
    const isUpdated = await editPage.isUpdated();

    assert.strictEqual(isUpdated, true);
  });

  Then('the user shouldn\'t be notified that modification have been made', async function () {
    const editPage = await await getElement(this.page, 'Edit');
    const isSaved = await editPage.isSaved();

    assert.strictEqual(isSaved, true);
  });

  Then('the user should see the confirmation dialog and dismiss', function () {
    this.page.on('dialog', async (dialog) => {
      assert.strictEqual(dialog.type(), 'confirm');
      await dialog.dismiss();
    });
  });

  Then('the user should see the confirmation dialog and accept', function () {
    this.page.on('dialog', async (dialog) => {
      assert.strictEqual(dialog.type(), 'confirm');
      await dialog.accept();
    });
  });
};
