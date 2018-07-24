const Header = require('../../decorators/enriched-entity/app/header.decorator');
const Sidebar = require('../../decorators/enriched-entity/app/sidebar.decorator');
const Modal = require('../../decorators/enriched-entity/create/modal.decorator');
const Grid = require('../../decorators/record/index/grid.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {answerJson, convertItemTable}
} = require(path.resolve(
  process.cwd(),
  './tests/front/acceptance/cucumber/test-helpers.js'
));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Sidebar: {
      selector: '.AknColumn',
      decorator: Sidebar
    },
    Header: {
      selector: '.AknTitleContainer',
      decorator: Header
    },
    Modal: {
      selector: '.modal--fullPage',
      decorator: Modal
    },
    Grid: {
      selector: '.AknGrid',
      decorator: Grid
    }
  };

  const getElement = createElementDecorator(config);

  const saveRecord = async function (page) {
    page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity/designer/record' === request.url() && 'POST' === request.method()) {
        answerJson(request, {}, 204);
      }
    })
  };

  const listRecordUpdated = async function (page, enrichedEntityIdentifier, identifier, labels) {
    page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity/designer/record' === request.url() && 'GET' === request.method()) {
        answerJson(request, {
          items: [{
            identifier: {identifier, enriched_entity_identifier: enrichedEntityIdentifier},
            enriched_entity_identifier: enrichedEntityIdentifier,
            code: identifier,
            labels: labels
          }], total: 1000
        });
      }
    });
  };

  const validationMessageShown = async function(page, message) {
    page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity/designer/record' === request.url() && 'POST' === request.method()) {
        answerJson(request, [{
          'messageTemplate': 'pim_enriched_entity.enriched_entity.validation.identifier.pattern',
          'parameters': {'{{ value }}': '\u0022invalid\/identifier\u0022'},
          'plural': null,
          'message': message,
          'root': {'identifier': 'invalid\/identifier', 'labels': []},
          'propertyPath': 'identifier',
          'invalidValue': 'invalid\/identifier',
          'constraint': {'defaultOption': null, 'requiredOptions': [], 'targets': 'property', 'payload': null},
          'cause': null,
          'code': null
        }], 400);
      }
    });
  };

  When('the user creates a record of {string} with:', async function (enrichedEntityIdentifier, updates) {
    const record = convertItemTable(updates)[0];

    const sidebar = await await getElement(this.page, 'Sidebar');
    await sidebar.clickOnTab('pim-enriched-entity-edit-form-records');

    const header = await await getElement(this.page, 'Header');
    await header.clickOnCreateButton();

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_enriched_entity.record.create.input.code', record.identifier);
    if (record.labels !== undefined && record.labels.en_US !== undefined) {
      await modal.fillField('pim_enriched_entity.record.create.input.label', record.labels.en_US);
    }
  });

  When('the user saves the record', async function () {
    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  Then('there is a record of {string} with:', async function (identifier, updates) {
    const record = convertItemTable(updates)[0];

    await listRecordUpdated(this.page, identifier, record.identifier, record.labels);

    const grid = await await getElement(this.page, 'Grid');
    await grid.hasRow(record.identifier);

    if (record.labels !== undefined && record.labels.en_US !== undefined) {
      const label = await grid.getRecordLabel(record.identifier);
      assert.strictEqual(label, record.labels.en_US);
    }
  });

  Then('the record validation error will be {string}', async function (expectedMessage) {
    await validationMessageShown(this.page, expectedMessage);
  });

  Then('the record will be saved', async function () {
    await saveRecord(this.page);
  });
};
