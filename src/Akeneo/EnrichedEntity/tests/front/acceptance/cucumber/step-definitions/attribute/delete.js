const AttributeBuilder = require('../../../../common/builder/attribute.js');
const Grid = require('../../decorators/attribute/grid.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {convertDataTable, convertItemTable},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function (cucumber) {
  const {Given, When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Grid: {
      selector: '.AknGrid',
      decorator: Grid
    },
  };

  const getElement = createElementDecorator(config);

  Then('the attribute {string} for the enriched entity {string} will be deleted', async function (
    attributeIdentifier,
    enrichedEntityIdentifier
  ) {
    this.page.on('request', request => {
      if (`http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/attribute/${attributeIdentifier}` === request.url() && 'DELETE' === request.method()) {
        request.respond({status: 204});
      }
    });
  });

  When('the user deletes the attribute {string} for the enriched entity {string}', async function (
    attributeIdentifier,
    enrichedEntityIdentifier
  ) {
    this.page.on('request', request => {
      if (`http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/attribute/${attributeIdentifier}` === request.url() && 'DELETE' === request.method()) {
        request.respond({status: 204});
      }
    });
  });
}
