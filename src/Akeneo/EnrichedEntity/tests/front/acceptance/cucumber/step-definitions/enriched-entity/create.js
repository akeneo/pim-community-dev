const Header = require('../../decorators/enriched-entity/app/header.decorator');
const Modal = require('../../decorators/enriched-entity/create/modal.decorator');
const Grid = require('../../decorators/enriched-entity/index/grid.decorator');
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
    Header: {
      selector: '.AknTitleContainer',
      decorator: Header
    },
    Modal: {
      selector: '.modal--fullPage',
      decorator: Modal
    },
    Grid: {
      selector: '.AknGridContainer',
      decorator: Grid
    }
  };

  const getElement = createElementDecorator(config);

  const saveEnrichedEntity = async function (page) {
    page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity' === request.url() && 'POST' === request.method()) {
        answerJson(request, {}, 204);
      }
    })
  };

  const listEnrichedUpdated = async function (page, identifier, labels) {
    page.once('request', request => {
      if ('http://pim.com/rest/enriched_entity' === request.url()) {
        answerJson(request, {
          items: [{
            identifier: identifier,
            labels: labels
          }], total: 1000
        });
      }
    });
  };

  When('the user creates an enriched entity {string} with:', async function (identifier, updates) {
    const enrichedEntity = convertItemTable(updates)[0];

    await saveEnrichedEntity(this.page);

    await this.page.evaluate(async () => {
      const Controller = require('pim/controller/enriched-entity/list');
      const controller = new Controller();
      controller.renderRoute();
      await document.getElementById('app').appendChild(controller.el);
    });

    const header = await await getElement(this.page, 'Header');
    await header.clickOnCreateButton();

    const modal = await await getElement(this.page, 'Modal');
    await modal.setCode(identifier);
    await modal.setLabel(enrichedEntity.labels.en_US);
    await modal.save();
  });

  Then('there is an enriched entity {string} with:', async function (identifier, updates) {
    const enrichedEntity = convertItemTable(updates)[0];

    await listEnrichedUpdated(this.page, identifier, enrichedEntity.labels);

    const grid = await await getElement(this.page, 'Grid');
    await grid.hasRow(identifier);

    const label = await grid.getEnrichedEntityLabel(identifier);
    assert.strictEqual(label, enrichedEntity.labels.en_US);
  });
};
