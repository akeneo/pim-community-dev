const Header = require('../../decorators/enriched-entity/app/header.decorator');
const Sidebar = require('../../decorators/enriched-entity/app/sidebar.decorator');
const Modal = require('../../decorators/enriched-entity/create/modal.decorator');
const Records = require('../../decorators/enriched-entity/edit/records.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {answerJson, convertItemTable},
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
    Modal: {
      selector: '.modal--fullPage',
      decorator: Modal,
    },
    Records: {
      selector: '.AknDefault-mainContent',
      decorator: Records,
    },
  };

  const getElement = createElementDecorator(config);

  const saveRecord = async function(page) {
    page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity/designer/record' === request.url() && 'POST' === request.method()) {
        answerJson(request, {}, 204);
      }
    });
  };

  const listRecordUpdated = async function(page, enrichedEntityIdentifier, identifier, code, labels) {
    page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity/designer/record' === request.url() && 'GET' === request.method()) {
        answerJson(request, {
          items: [
            {
              identifier: identifier,
              enriched_entity_identifier: enrichedEntityIdentifier,
              code: code,
              labels: labels,
            },
          ],
          total: 1000,
        });
      }
    });
  };

  const validationMessageShown = async function(page, message) {
    page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity/designer/record' === request.url() && 'POST' === request.method()) {
        answerJson(
          request,
          [
            {
              messageTemplate: 'pim_enriched_entity.enriched_entity.validation.identifier.pattern',
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

  const getRecordIdentifier = function(enrichedEntityIdentifier, code) {
    return `${enrichedEntityIdentifier}_${code}_123456`;
  };

  When('the user creates a record of {string} with:', async function(enrichedEntityIdentifier, updates) {
    const record = convertItemTable(updates)[0];

    const sidebar = await await getElement(this.page, 'Sidebar');
    await sidebar.clickOnTab('record');

    const header = await await getElement(this.page, 'Header');
    await header.clickOnCreateButton();

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_enriched_entity.record.create.input.code', record.code);
    if (record.labels !== undefined && record.labels.en_US !== undefined) {
      await modal.fillField('pim_enriched_entity.record.create.input.label', record.labels.en_US);
    }
  });

  When('the user saves the record', async function() {
    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  Then('there is a record of {string} with:', async function(enrichedEntityIdentifier, updates) {
    const record = convertItemTable(updates)[0];
    const recordIdentifier = getRecordIdentifier(enrichedEntityIdentifier, record.code);

    await listRecordUpdated(this.page, enrichedEntityIdentifier, recordIdentifier, record.code, record.labels);

    const records = await await getElement(this.page, 'Records');
    await records.hasRecord(recordIdentifier);

    if (record.labels !== undefined && record.labels.en_US !== undefined) {
      const label = await records.getRecordLabel(recordIdentifier);
      assert.strictEqual(label, record.labels.en_US);
    }
  });

  Then('the record validation error will be {string}', async function(expectedMessage) {
    await validationMessageShown(this.page, expectedMessage);
  });

  Then('the record will be saved', async function() {
    await saveRecord(this.page);
  });
};
