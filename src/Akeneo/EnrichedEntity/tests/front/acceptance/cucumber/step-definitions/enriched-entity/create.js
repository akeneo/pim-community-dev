const Header = require('../../decorators/enriched-entity/app/header.decorator');
const Modal = require('../../decorators/enriched-entity/create/modal.decorator');
const Grid = require('../../decorators/enriched-entity/index/grid.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {answerJson, convertItemTable},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Header: {
      selector: '.AknTitleContainer',
      decorator: Header,
    },
    Modal: {
      selector: '.modal--fullPage',
      decorator: Modal,
    },
    Grid: {
      selector: '.AknGridContainer',
      decorator: Grid,
    },
  };

  const getElement = createElementDecorator(config);

  const saveEnrichedEntity = async function(page) {
    page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity' === request.url() && 'POST' === request.method()) {
        answerJson(request, {}, 204);
      }
    });
  };

  const listEnrichedUpdated = async function(page, identifier, labels) {
    page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity' === request.url()) {
        answerJson(request, {
          items: [
            {
              identifier: identifier,
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
      if ('http://pim.com/rest/enriched_entity' === request.url() && 'POST' === request.method()) {
        answerJson(
          request,
          [
            {
              messageTemplate: 'pim_enriched_entity.enriched_entity.validation.identifier.pattern',
              parameters: {'{{ value }}': '\u0022invalid/identifier\u0022'},
              plural: null,
              message: message,
              root: {identifier: 'invalid/identifier', labels: []},
              propertyPath: 'identifier',
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

  When('the user creates an enriched entity {string} with:', async function(identifier, updates) {
    const enrichedEntity = convertItemTable(updates)[0];

    await this.page.evaluate(async () => {
      const Controller = require('pim/controller/enriched-entity/list');
      const controller = new Controller();
      controller.renderRoute();
      await document.getElementById('app').appendChild(controller.el);
    });

    const header = await await getElement(this.page, 'Header');
    await header.clickOnCreateButton();

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_enriched_entity.enriched_entity.create.input.code', identifier);
    if (enrichedEntity.labels !== undefined && enrichedEntity.labels.en_US !== undefined) {
      await modal.fillField('pim_enriched_entity.enriched_entity.create.input.label', enrichedEntity.labels.en_US);
    }
  });

  When('the user saves the enriched entity', async function() {
    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  Then('there is an enriched entity {string} with:', async function(identifier, updates) {
    const enrichedEntity = convertItemTable(updates)[0];

    listEnrichedUpdated(this.page, identifier, enrichedEntity.labels);

    const grid = await await getElement(this.page, 'Grid');
    await grid.hasRow(identifier);

    if (enrichedEntity.labels !== undefined && enrichedEntity.labels.en_US !== undefined) {
      const label = await grid.getEnrichedEntityLabel(enrichedEntity.identifier);
      assert.strictEqual(label, enrichedEntity.labels.en_US);
    }
  });

  Then('The validation error will be {string}', async function(expectedMessage) {
    await validationMessageShown(this.page, expectedMessage);
  });

  Then('the enriched entity will be saved', async function() {
    await saveEnrichedEntity(this.page);
  });

  Then('a validation message is displayed {string}', async function(expectedMessage) {
    const modal = await await getElement(this.page, 'Modal');
    const actualMesssage = await modal.getValidationMessageForCode();
    assert.strictEqual(expectedMessage, actualMesssage);
  });

  Then('the user should not be able to create an enriched entity', async function() {
    const header = await await getElement(this.page, 'Header');
    assert.strictEqual(false, await header.isCreateButtonVisible());
  });
};
