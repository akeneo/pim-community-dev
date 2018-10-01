const Header = require('../../decorators/enriched-entity/app/header.decorator');
const Modal = require('../../decorators/create/modal.decorator');
const Grid = require('../../decorators/enriched-entity/index/grid.decorator');
const {getRequestContract, listenRequest} = require('../../tools');
const path = require('path');

const {
  decorators: {createElementDecorator},
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

  const startCreate = async function(page) {
    await page.evaluate(async () => {
      const Controller = require('pim/controller/enriched-entity/edit');
      const controller = new Controller();
      controller.renderRoute({params: {identifier: 'designer', tab: 'attribute'}});
      await document.getElementById('app').appendChild(controller.el);
    });

    page.waitFor('.AknTitleContainer-title');
    const header = await await getElement(page, 'Header');
    await header.clickOnCreateButton();
  };

  When('the user creates a valid attribute', async function() {
    await startCreate(this.page);

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_enriched_entity.attribute.create.input.code', 'niceattribute');
    await modal.fillField('pim_enriched_entity.attribute.create.input.label', 'Nice attribute');
    await modal.switchField('pim_enriched_entity.attribute.create.input.value_per_channel', true);
  });

  When('the user creates an attribute with an invalid code', async function() {
    await startCreate(this.page);

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_enriched_entity.attribute.create.input.code', 'not so nice attribute');
    await modal.fillField('pim_enriched_entity.attribute.create.input.label', 'Nice attribute');
    await modal.switchField('pim_enriched_entity.attribute.create.input.value_per_channel', true);
  });

  Then('the user saves the valid attribute', async function() {
    const requestContract = getRequestContract('Attribute/Create/ok.json');

    await listenRequest(this.page, requestContract);

    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  Then('the user saves the attribute with an invalid code', async function() {
    const requestContract = getRequestContract('Attribute/Create/invalid_code.json');

    await listenRequest(this.page, requestContract);

    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  Then('the user should not see any validation error', async function() {
    const modal = await await getElement(this.page, 'Modal');
    const error = await modal.getValidationMessageForCode();

    assert.strictEqual(error, '');
  });

  Then('the user should see the validation error {string}', async function(expectedError) {
    const modal = await await getElement(this.page, 'Modal');
    const error = await modal.getValidationMessageForCode();

    assert.strictEqual(error, expectedError);
  });
};
