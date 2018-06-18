const Properties = require('../../decorators/enriched-entity/edit/properties.decorator');

const {
    decorators: { createElementDecorator }
} = require('../../test-helpers.js');

module.exports = async function (cucumber) {
  const { When } = cucumber;
  const assert = require('assert');

  const config = {
      'Properties': {
          selector: '.AknDefault-mainContent',
          decorator: Properties
      }
  };

  const getElement = createElementDecorator(config);

  When('the user asks for the enriched entity {string}', async function (identifier) {
    await this.page.evaluate(async (identifier) => {
        const Controller = require('pim/controller/enriched-entity/edit');
        const controller = new Controller();
        controller.renderRoute({params: { identifier }});
        await document.getElementById('app').appendChild(controller.el);
    }, identifier);


    await this.page.waitFor('.object-attributes');
    const properties = await (await getElement(this.page, 'Properties'));
    const isLoaded =  await properties.isLoaded();

    assert.equal(isLoaded, true);
  });

  When('the user gets the enriched entity {string} with label {string}', async function (
    expectedIdentifier,
    expectedLabel
  ) {
    const properties = await (await getElement(this.page, 'Properties'));
    const identifierValue = await properties.getIdentifier();
    assert.equal(identifierValue, expectedIdentifier);

    const labelValue = await properties.getLabel();
    assert.equal(labelValue, expectedLabel);
  });
};
