const AttributeBuilder = require('../../../../common/builder/attribute.js');
const path = require('path');

const {
  decorators: {},
  tools: {answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function (cucumber) {
  const {Given, When, Then} = cucumber;

  Given('the following text attributes for the enriched entity {string}:', function (
    enrichedEntityIdentifier,
    attributes
  ) {
    const attributesList = attributes.hashes().map(function (attribute) {
      const builder = (new AttributeBuilder('text'))
        .withCode(attribute.code)
        .withEnrichedEntityIdentifier(enrichedEntityIdentifier);

      if (undefined !== attribute.labels) {
        builder.withLabels(JSON.parse(attribute.labels));
      }

      return builder.build();
    });

    this.page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity/designer/attribute' === request.url()) {
        answerJson(request, {items: attributesList, total: 1000});
      }
    });
  });

  Then('there is no attribute {string} for the enriched entity {string}', function (
    attributeIdentifier,
    enrichedEntityIdentifier
  ) {
    this.page.on('request', request => {
      if (`http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/attribute/${attributeIdentifier}` === request.url() && 'GET' === request.method()) {
        request.respond({status: 404});
      }
    });
  });
}
