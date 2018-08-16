const Edit = require('../../decorators/enriched-entity/edit.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {convertDataTable, convertItemTable, answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
    },
  };

  const getElement = createElementDecorator(config);

  const askForEnrichedEntity = async function(identifier) {
    await this.page.evaluate(async identifier => {
      const Controller = require('pim/controller/enriched-entity/edit');
      const controller = new Controller();
      controller.renderRoute({params: {identifier}});
      await document.getElementById('app').appendChild(controller.el);
    }, identifier);

    await this.page.waitFor('.AknDefault-mainContent[data-tab="pim-enriched-entity-edit-form-attribute"] .content');
    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    const isLoaded = await properties.isLoaded();

    assert.strictEqual(isLoaded, true);
  };

  const changeEnrichedEntity = async function(editPage, identifier, updates) {
    const properties = await editPage.getProperties();

    const labels = convertDataTable(updates).labels;

    Object.keys(labels).forEach(async locale => {
      const label = labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);
      await properties.setLabel(label);
    });
  };

  const savedEnrichedEntityWillBe = function(page, identifier, updates) {
    page.on('request', request => {
      if (`http://pim.com/rest/enriched_entity/${identifier}` === request.url() && 'POST' === request.method()) {
        answerJson(request, {}, 204);
      }

      if (`http://pim.com/rest/enriched_entity/${identifier}` === request.url() && 'GET' === request.method()) {
        answerJson(request, convertItemTable(updates)[0], 200);
      }
    });
  };

  const answerLocaleList = function() {
    this.page.on('request', request => {
      if ('http://pim.com/configuration/locale/rest?activated=true' === request.url() && 'GET' === request.method()) {
        answerJson(
          request,
          [
            {code: 'de_DE', label: 'German (Germany)', region: 'Germany', language: 'German'},
            {code: 'en_US', label: 'English (United States)', region: 'United States', language: 'English'},
            {code: 'fr_FR', label: 'French (France)', region: 'France', language: 'French'},
          ],
          200
        );
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
    await answerLocaleList.apply(this);
    await askForEnrichedEntity.apply(this, [identifier]);

    const editPage = await await getElement(this.page, 'Edit');
    await changeEnrichedEntity(editPage, identifier, updates);
    await savedEnrichedEntityWillBe(this.page, identifier, updates);
    await editPage.save();
  });

  When('the user changes the enriched entity {string} with:', async function(identifier, updates) {
    await answerLocaleList.apply(this);
    await askForEnrichedEntity.apply(this, [identifier]);

    const editPage = await await getElement(this.page, 'Edit');

    await changeEnrichedEntity.apply(this, [editPage, identifier, updates]);
  });

  Then('the enriched entity {string} should be:', async function(identifier, updates) {
    const enrichedEntity = convertItemTable(updates)[0];

    const editPage = await await getElement(this.page, 'Edit');
    const properties = await editPage.getProperties();
    const identifierValue = await properties.getIdentifier();
    assert.strictEqual(identifierValue, enrichedEntity.identifier);

    await Object.keys(enrichedEntity.labels).forEach(async locale => {
      const label = enrichedEntity.labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);
      const labelValue = await properties.getLabel();
      assert.strictEqual(labelValue, label);
    });
  });

  Then('the saved enriched entity {string} will be:', async function(identifier, updates) {
    await savedEnrichedEntityWillBe(this.page, identifier, updates);
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

  Then('the enriched entity {string} save will fail', function(identifier) {
    this.page.on('request', request => {
      if (`http://pim.com/rest/enriched_entity/${identifier}` === request.url() && 'POST' === request.method()) {
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
};
