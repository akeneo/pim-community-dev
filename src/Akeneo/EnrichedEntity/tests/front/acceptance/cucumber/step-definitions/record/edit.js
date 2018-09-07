const RecordBuilder = require('../../../../common/builder/record.js');
const Edit = require('../../decorators/record/edit.decorator');
const Header = require('../../decorators/enriched-entity/app/header.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {convertDataTable, convertItemTable, answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

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
  };

  const getElement = createElementDecorator(config);

  const askForRecord = async function(recordCode, enrichedEntityIdentifier) {
    await this.page.evaluate(
      async (enrichedEntityIdentifier, recordCode) => {
        const Controller = require('pim/controller/record/edit');
        const controller = new Controller();
        controller.renderRoute({params: {enrichedEntityIdentifier, recordCode, tab: 'enrich'}});

        await document.getElementById('app').appendChild(controller.el);
      },
      enrichedEntityIdentifier,
      recordCode
    );

    await this.page.waitFor('.AknDefault-mainContent[data-tab="enrich"] .content');
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isLoaded = await enrich.isLoaded();

    assert.strictEqual(isLoaded, true);
  };

  const updateRecord = async function(editPage, updates) {
    const enrich = await editPage.getEnrich();

    const labels = convertDataTable(updates).labels;

    for (locale in labels) {
      const label = labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);

      return await enrich.setLabel(label);
    }
  };

  const savedRecordWillBe = function(page, enrichedEntityIdentifier, recordCode, updates) {
    page.on('request', request => {
      if (
        `http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/record/${recordCode}` === request.url() &&
        'POST' === request.method()
      ) {
        answerJson(request, {}, 204);
      }

      if (
        `http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/record/${recordCode}` === request.url() &&
        'GET' === request.method()
      ) {
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
  const givenRecords = function(records) {
    const recordResponse = records.hashes().map(function(record) {
      const recordBuilder = new RecordBuilder();

      if (undefined !== record['enriched entity']) {
        recordBuilder.withEnrichedEntityIdentifier(record['enriched entity']);
      }
      if (undefined !== record.code) {
        recordBuilder.withCode(record.code);
      }
      if (undefined !== record.labels) {
        recordBuilder.withLabels(JSON.parse(record.labels));
      }
      if (undefined !== record.image) {
        recordBuilder.withImage(JSON.parse(record.image));
      } else {
        recordBuilder.withImage(null);
      }

      return recordBuilder.build();
    });

    recordResponse.forEach(record => {
      this.page.on('request', request => {
        if (
          `http://pim.com/rest/enriched_entity/${record['enriched_entity_identifier']}/record/${record.code}` ===
            request.url() &&
          'GET' === request.method()
        ) {
          answerJson(request, record);
        }
      });
    });
  };
  Given('the following record:', givenRecords);

  When('the user asks for the record {string} of enriched entity {string}', askForRecord);

  When('the user gets the record {string} with label {string}', async function(expectedIdentifier, expectedLabel) {
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const identifierValue = await enrich.getIdentifier();
    assert.strictEqual(identifierValue, expectedIdentifier);

    const labelValue = await enrich.getLabel();
    assert.strictEqual(labelValue, expectedLabel);
  });

  When('the user updates the record {string} of enriched entity {string} with:', async function(
    recordCode,
    enrichedEntityIdentifier,
    updates
  ) {
    await answerLocaleList.apply(this);
    await askForRecord.apply(this, [recordCode, enrichedEntityIdentifier]);

    const editPage = await await getElement(this.page, 'Edit');
    await updateRecord(editPage, updates);
    await savedRecordWillBe(this.page, enrichedEntityIdentifier, recordCode, updates);
    await editPage.save();
  });

  When('the user changes the record {string} of enriched entity with:', async function(
    recordIdentifier,
    enrichedEntityIdentifier,
    updates
  ) {
    await answerLocaleList.apply(this);
    await askForRecord.apply(this, [recordIdentifier, enrichedEntityIdentifier]);

    const editPage = await await getElement(this.page, 'Edit');

    await updateRecord.apply(this, [editPage, updates]);
  });

  Then('the record {string} should be:', async function(code, updates) {
    const record = convertItemTable(updates)[0];

    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const codeValue = await enrich.getIdentifier();
    assert.strictEqual(codeValue, record.code);

    for (locale in record.labels) {
      const label = record.labels[locale];
      await (await editPage.getLocaleSwitcher()).switchLocale(locale);
      const labelValue = await await enrich.getLabel();

      assert.strictEqual(labelValue, label);
    }
  });

  Then('the saved record {string} of enriched entity {string} will be:', async function(
    recordCode,
    enrichedEntityIdentifier,
    updates
  ) {
    await savedRecordWillBe(this.page, enrichedEntityIdentifier, recordCode, updates);
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

  Then('the record {string} of enriched entity {string} save will fail', function(
    recordCode,
    enricdhedEntityIdentifier
  ) {
    this.page.on('request', request => {
      if (
        `http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/record/${recordCode}` === request.url() &&
        'POST' === request.method()
      ) {
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

  When('the user deletes the record {string} of enriched entity {string}', async function(
    recordCode,
    enrichedEntityIdentifier
  ) {
    const header = await await getElement(this.page, 'Header');

    this.page.once('request', request => {
      if (
        `http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/record/${recordCode}` === request.url() &&
        'DELETE' === request.method()
      ) {
        request.respond({
          status: 204,
          contentType: 'application/json',
          body: null,
        });
      }
    });

    this.page.once('dialog', async dialog => {
      await dialog.accept();
    });

    header.clickOnDeleteButton();
  });

  When('the user refuses to delete the current record', async function() {
    const header = await await getElement(this.page, 'Header');

    this.page.once('dialog', async dialog => {
      await dialog.dismiss();
    });

    header.clickOnDeleteButton();
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
};
