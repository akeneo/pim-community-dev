const path = require('path');
const AttributeEdit = require('../../decorators/reference-entity/edit/attribute/edit.decorator');
const Edit = require('../../decorators/reference-entity/edit.decorator');

const {
  decorators: {createElementDecorator},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {Then} = cucumber;
  const assert = require('assert');

  const config = {
    AttributeEdit: {
      selector: '.AknDefault-mainContent .AknQuickEdit',
      decorator: AttributeEdit,
    },
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
    },
  };

  const getElement = createElementDecorator(config);

  Then('the user edits the attribute property {string} with value {string}', async function(property, value) {
    const attributeEdit = await getElement(this.page, 'AttributeEdit');
    await attributeEdit[`set${property}`](value);
  });

  Then("the user can't edit the attribute property {string}", async function(property) {
    const attributeEdit = await getElement(this.page, 'AttributeEdit');
    await attributeEdit[`disabled${property}`]();
  });

  Then('the attribute property {string} should not be visible', async function(property) {
    const attributeEdit = await getElement(this.page, 'AttributeEdit');
    const isVisible = await attributeEdit.isVisible(property);

    assert.strictEqual(isVisible, false);
  });
};
