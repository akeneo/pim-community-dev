const path = require('path');
const Header = require('../../decorators/reference-entity/app/header.decorator');
const Edit = require('../../decorators/record/edit.decorator');
const Modal = require('../../decorators/delete/modal.decorator');
const {getRequestContract, listenRequest} = require('../../tools');

const {
  decorators: {createElementDecorator}
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
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
    Modal: {
      selector: '.AknFullPage--modal',
      decorator: Modal,
    },
  };

  const getElement = createElementDecorator(config);

  const askForRecord = async function(recordCode, referenceEntityIdentifier) {
    await this.page.evaluate(
      async (referenceEntityIdentifier, recordCode) => {
        const Controller = require('pim/controller/record/edit');
        const controller = new Controller();
        controller.renderRoute({params: {referenceEntityIdentifier, recordCode, tab: 'enrich'}});
        await document.getElementById('app').appendChild(controller.el);
      },
      referenceEntityIdentifier,
      recordCode
    );
    await this.page.waitFor('.AknDefault-mainContent[data-tab="enrich"] .content');
    const editPage = await await getElement(this.page, 'Edit');
    const enrich = await editPage.getEnrich();
    const isLoaded = await enrich.isLoaded();
    assert.strictEqual(isLoaded, true);
  };


};
