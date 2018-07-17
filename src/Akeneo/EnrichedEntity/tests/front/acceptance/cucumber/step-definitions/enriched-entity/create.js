const Header = require('../../decorators/enriched-entity/header.decorator');

const {
  decorators: {createElementDecorator}
} = require(path.resolve(
  process.cwd(),
  './tests/front/acceptance/cucumber/test-helpers.js'
));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Grid: {
      selector: '.AknTitleContainer',
      decorator: Header
    }
  };

  const getElement = createElementDecorator(config);

  When('the user creates an enriched entity {string} with:', async function (string, dataTable) {
    await this.page.evaluate(async () => {
      const Controller = require('pim/controller/enriched-entity/list');
      const controller = new Controller();
      controller.renderRoute();
      await document.getElementById('app').appendChild(controller.el);
    });

    const header = await await getElement(this.page, 'Header');
    await header.clickOnCreateButton();


  });

  Then('there is an enriched entity {string} with:', function (string, dataTable) {
    // Write code here that turns the phrase above into concrete actions
    return 'pending';
  });
};
