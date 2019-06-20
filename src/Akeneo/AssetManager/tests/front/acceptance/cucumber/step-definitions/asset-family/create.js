const Header = require('../../decorators/reference-entity/app/header.decorator');
const Modal = require('../../decorators/create/modal.decorator');
const Grid = require('../../decorators/reference-entity/index/grid.decorator');
const {getRequestContract, listenRequest} = require('../../tools');
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
      selector: '.modal',
      decorator: Modal,
    },
    Grid: {
      selector: '.AknGridContainer',
      decorator: Grid,
    },
  };

  const getElement = createElementDecorator(config);

  const saveReferenceEntity = async function(page) {
    const requestContract = getRequestContract('ReferenceEntity/Create/ok.json');

    return await listenRequest(page, requestContract);
  };

  const listReferenceEntityUpdated = async function(page, identifier, labels) {
    page.on('request', request => {
      if ('http://pim.com/rest/reference_entity' === request.url()) {
        answerJson(request, {
          items: [
            {
              identifier: identifier,
              labels: labels,
            },
          ],
          matches_count: 1000,
        });
      }
    });
  };

  const validationMessageShown = async function(page, message) {
    page.on('request', request => {
      if ('http://pim.com/rest/reference_entity' === request.url() && 'POST' === request.method()) {
        answerJson(
          request,
          [
            {
              messageTemplate: 'pim_reference_entity.reference_entity.validation.code.pattern',
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

  When('the user creates a reference entity {string} with:', async function(identifier, updates) {
    const referenceEntity = convertItemTable(updates)[0];

    await this.page.evaluate(async () => {
      const Controller = require('pim/controller/reference-entity/list');
      const controller = new Controller();
      controller.renderRoute();
      await document.getElementById('app').appendChild(controller.el);
    });

    const header = await await getElement(this.page, 'Header');
    await header.clickOnCreateButton();

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_reference_entity.reference_entity.create.input.code', identifier);
    if (referenceEntity.labels !== undefined && referenceEntity.labels.en_US !== undefined) {
      await modal.fillField('pim_reference_entity.reference_entity.create.input.label', referenceEntity.labels.en_US);
    }
  });

  When('the user saves the reference entity', async function() {
    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  Then('there is a reference entity {string} with:', async function(identifier, updates) {
    const referenceEntity = convertItemTable(updates)[0];

    listReferenceEntityUpdated(this.page, identifier, referenceEntity.labels);

    const grid = await await getElement(this.page, 'Grid');
    await grid.hasRow(identifier);

    if (referenceEntity.labels !== undefined && referenceEntity.labels.en_US !== undefined) {
      const label = await grid.getReferenceEntityLabel(referenceEntity.identifier);
      assert.strictEqual(label, referenceEntity.labels.en_US);
    }
  });

  Then('The validation error will be {string}', async function(expectedMessage) {
    await validationMessageShown(this.page, expectedMessage);
  });

  Then('the reference entity will be saved', async function() {
    await saveReferenceEntity(this.page);
  });

  Then('a validation message is displayed {string}', async function(expectedMessage) {
    const modal = await await getElement(this.page, 'Modal');
    const actualMesssage = await modal.getValidationMessageForCode();
    assert.strictEqual(expectedMessage, actualMesssage);
  });

  Then('the user should not be able to create a reference entity', async function() {
    const header = await await getElement(this.page, 'Header');
    assert.strictEqual(false, await header.isCreateButtonVisible());
  });
};
