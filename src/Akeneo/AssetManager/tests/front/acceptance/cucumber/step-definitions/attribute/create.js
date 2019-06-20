const Header = require('../../decorators/asset-family/app/header.decorator');
const Modal = require('../../decorators/create/modal.decorator');
const Grid = require('../../decorators/asset-family/index/grid.decorator');
const {getRequestContract, listenRequest, answerChannelList} = require('../../tools');
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
      selector: '.modal',
      decorator: Modal,
    },
    Grid: {
      selector: '.AknGridContainer',
      decorator: Grid,
    },
  };

  const getElement = createElementDecorator(config);

  const loadAttributeTab = async function(page) {
    await page.evaluate(async () => {
      const Controller = require('pim/controller/asset-family/edit');
      const controller = new Controller();
      controller.renderRoute({params: {identifier: 'designer', tab: 'attribute'}});
      await document.getElementById('app').appendChild(controller.el);
    });

    page.waitFor('.AknTitleContainer-title');
  };

  const startCreate = async function(page) {
    await loadAttributeTab(page);

    const header = await await getElement(page, 'Header');
    await header.clickOnCreateButton();
  };

  When('the user creates a valid attribute', async function() {
    await answerChannelList.apply(this);
    await startCreate(this.page);

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_asset_manager.attribute.create.input.code', 'niceattribute');
    await modal.fillField('pim_asset_manager.attribute.create.input.label', 'Nice attribute');
    await modal.switchField('pim_asset_manager.attribute.create.input.value_per_channel', true);
  });

  When('the user creates an attribute with an invalid code', async function() {
    await answerChannelList.apply(this);
    await startCreate(this.page);

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_asset_manager.attribute.create.input.code', 'not so nice attribute');
    await modal.fillField('pim_asset_manager.attribute.create.input.label', 'Nice attribute');
    await modal.switchField('pim_asset_manager.attribute.create.input.value_per_channel', true);
  });

  Then('the user saves the valid attribute', async function() {
    const requestContract = getRequestContract('Attribute/Create/attribute_text_ok.json');

    await listenRequest(this.page, requestContract);

    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  When('the user creates a valid asset attribute', async function() {
    await answerChannelList.apply(this);
    await startCreate(this.page);

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_asset_manager.attribute.create.input.code', 'niceattribute');
    await modal.fillField('pim_asset_manager.attribute.create.input.label', 'Nice attribute');
    await modal.select('.AknFieldContainer[data-code="type"]', 'asset');
    await modal.switchField('pim_asset_manager.attribute.create.input.value_per_channel', true);
  });

  Then('the user saves the valid asset attribute', async function() {
    const requestContract = getRequestContract('Attribute/Create/attribute_asset_ok.json');

    await listenRequest(this.page, requestContract);

    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  When('the user creates a valid asset collection attribute', async function() {
    await answerChannelList.apply(this);
    await startCreate(this.page);

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_asset_manager.attribute.create.input.code', 'niceattribute');
    await modal.fillField('pim_asset_manager.attribute.create.input.label', 'Nice attribute');
    await modal.select('.AknFieldContainer[data-code="type"]', 'asset_collection');
    await modal.switchField('pim_asset_manager.attribute.create.input.value_per_channel', true);
  });

  Then('the user saves the valid asset collection attribute', async function() {
    const requestContract = getRequestContract('Attribute/Create/attribute_asset_collection_ok.json');

    await listenRequest(this.page, requestContract);

    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  When('the user creates a valid image attribute', async function() {
    await answerChannelList.apply(this);
    await startCreate(this.page);

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_asset_manager.attribute.create.input.code', 'niceattribute');
    await modal.fillField('pim_asset_manager.attribute.create.input.label', 'Nice attribute');
    await modal.select('.AknFieldContainer[data-code="type"]', 'image');
    await modal.switchField('pim_asset_manager.attribute.create.input.value_per_channel', true);
  });

  Then('the user saves the valid image attribute', async function() {
    const requestContract = getRequestContract('Attribute/Create/attribute_image_ok.json');

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

    let error = null;
    while (error !== '') {
      error = await modal.getValidationMessageForCode();
    }

    assert.strictEqual(error, '');
  });

  Then('the user should see the validation error {string}', async function(expectedError) {
    const modal = await await getElement(this.page, 'Modal');

    let error = null;
    while (error !== expectedError) {
      error = await modal.getValidationMessageForCode();
    }
  });

  When('the user creates a valid option attribute', async function() {
    await answerChannelList.apply(this);
    await startCreate(this.page);

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_asset_manager.attribute.create.input.code', 'niceattribute');
    await modal.fillField('pim_asset_manager.attribute.create.input.label', 'Nice attribute');
    await modal.select('.AknFieldContainer[data-code="type"]', 'option');
    await modal.switchField('pim_asset_manager.attribute.create.input.value_per_channel', true);
  });

  Then('the user saves the valid option attribute', async function() {
    const requestContract = getRequestContract('Attribute/Create/attribute_option_ok.json');

    await listenRequest(this.page, requestContract);

    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  When('the user creates a valid option collection attribute', async function() {
    await answerChannelList.apply(this);
    await startCreate(this.page);

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_asset_manager.attribute.create.input.code', 'niceattribute');
    await modal.fillField('pim_asset_manager.attribute.create.input.label', 'Nice attribute');
    await modal.select('.AknFieldContainer[data-code="type"]', 'option_collection');
    await modal.switchField('pim_asset_manager.attribute.create.input.value_per_channel', true);
  });

  Then('the user saves the valid option collection attribute', async function() {
    const requestContract = getRequestContract('Attribute/Create/attribute_option_collection_ok.json');

    await listenRequest(this.page, requestContract);

    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });

  Then('the user should not see the add attribute button', async function() {
    await answerChannelList.apply(this);
    await loadAttributeTab(this.page);
    const header = await await getElement(this.page, 'Header');
    await header.hasNoCreateButton();
  });

  When('the user creates a valid number attribute', async function() {
    await answerChannelList.apply(this);
    await startCreate(this.page);

    const modal = await await getElement(this.page, 'Modal');
    await modal.fillField('pim_asset_manager.attribute.create.input.code', 'niceattribute');
    await modal.fillField('pim_asset_manager.attribute.create.input.label', 'Nice attribute');
    await modal.select('.AknFieldContainer[data-code="type"]', 'number');
  });

  Then('the user saves the valid number attribute', async function() {
    const requestContract = getRequestContract('Attribute/Create/attribute_number_ok.json');

    await listenRequest(this.page, requestContract);

    const modal = await await getElement(this.page, 'Modal');
    await modal.save();
  });
};
