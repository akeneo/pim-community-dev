const path = require('path');
const Sidebar = require('../../decorators/enriched-entity/app/sidebar.decorator');
const Attributes = require('../../decorators/enriched-entity/edit/attributes.decorator');

const {
  decorators: {createElementDecorator},
  tools: {answerJson}
} = require(path.resolve(
    process.cwd(),
    './tests/front/acceptance/cucumber/test-helpers.js'
));

module.exports = async function(cucumber) {
  const {Given, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Sidebar: {
      selector: '.AknColumn',
      decorator: Sidebar
    },
    Attributes: {
      selector: '.AknDefault-mainContent .AknSubsection',
      decorator: Attributes,
    }
  };

  const getElement = createElementDecorator(config);

  const showAttributesTab = async function (page) {
    const sidebar = await await getElement(page, 'Sidebar');
    await sidebar.clickOnTab('pim-enriched-entity-edit-form-attribute');
  };

  Given('the following attributes for the enriched entity {string}:',
      async function (enrichedEntityIdentifier, attributes) {
        const attributesSaved = attributes.hashes().map((normalizedAttribute) => {
          if ('text' === normalizedAttribute.type) {
            return {
              identifier: {
                identifier: normalizedAttribute.code,
                enriched_entity_identifier: enrichedEntityIdentifier,
              },
              enriched_entity_identifier: enrichedEntityIdentifier,
              code: normalizedAttribute.code,
              required: false,
              order: 0,
              value_per_locale: true,
              value_per_channel: false,
              type: 'text',
              labels: JSON.parse(normalizedAttribute.labels),
              max_length: 255,
            };
          } else if ('image' === normalizedAttribute.type) {
            return {
              identifier: {
                identifier: normalizedAttribute.code,
                enriched_entity_identifier: enrichedEntityIdentifier,
              },
              enriched_entity_identifier: enrichedEntityIdentifier,
              code: normalizedAttribute.code,
              required: false,
              order: 1,
              value_per_locale: true,
              value_per_channel: false,
              type: 'image',
              labels: JSON.parse(normalizedAttribute.labels),
              max_file_size: '124.12',
              allowed_extensions: ['png', 'jpg']
            };
          } else {
            throw new Error(`Attribute of type "${normalizedAttribute.type}" not supported.`)
          }
        });

        this.page.on('request', (request) => {
          if (`http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/attribute` === request.url() &&
              'GET' === request.method()
          ) {
            answerJson(request, attributesSaved);
          }
        });
      }
  );

  Then('there should be the following attributes:', async function (expectedAttributes) {
    await showAttributesTab(this.page);

    const attributes = await await getElement(this.page, 'Attributes');
    const isValid = await expectedAttributes.hashes().reduce(async (isValid, expectedAttribute) => {
      return await isValid && await attributes.hasAttribute(expectedAttribute.code, expectedAttribute.type);
    }, true);
    assert.strictEqual(isValid, true);
  });

  Then('the list of attributes should be empty', async function () {
    await showAttributesTab(this.page);

    const attributes = await await getElement(this.page, 'Attributes');
    const isEmpty = await attributes.isEmpty();

    assert.strictEqual(isEmpty, true);
  });
};
