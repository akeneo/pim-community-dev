const path = require('path');
const Sidebar = require('../../decorators/enriched-entity/app/sidebar.decorator');
const Attributes = require('../../decorators/enriched-entity/edit/attributes.decorator');

const {
  decorators: {createElementDecorator},
  tools: {answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {Given, Then, When} = cucumber;
  const assert = require('assert');

  const config = {
    Sidebar: {
      selector: '.AknColumn',
      decorator: Sidebar,
    },
    Attributes: {
      selector: '.AknDefault-mainContent .AknSubsection',
      decorator: Attributes,
    },
  };

  const getElement = createElementDecorator(config);

  const showAttributesTab = async function(page) {
    const sidebar = await await getElement(page, 'Sidebar');
    await sidebar.clickOnTab('pim-enriched-entity-edit-form-attribute');
  };

  Given('the following attributes for the enriched entity {string}:', async function(
    enrichedEntityIdentifier,
    attributes
  ) {
    const attributesSaved = attributes.hashes().map(normalizedAttribute => {
      if ('text' === normalizedAttribute.type) {
        return {
          identifier: {
            identifier: normalizedAttribute.code,
            enriched_entity_identifier: enrichedEntityIdentifier,
          },
          enriched_entity_identifier: enrichedEntityIdentifier,
          code: normalizedAttribute.code,
          is_required: false,
          order: 0,
          value_per_locale: true,
          value_per_channel: false,
          type: 'text',
          labels: JSON.parse(normalizedAttribute.labels),
          max_length: 255,
          is_text_area: false,
          is_rich_text_editor: false,
          validation_rule: 'none',
          regular_expression: null,
        };
      } else if ('image' === normalizedAttribute.type) {
        return {
          identifier: {
            identifier: normalizedAttribute.code,
            enriched_entity_identifier: enrichedEntityIdentifier,
          },
          enriched_entity_identifier: enrichedEntityIdentifier,
          code: normalizedAttribute.code,
          is_required: false,
          order: 1,
          value_per_locale: true,
          value_per_channel: false,
          type: 'image',
          labels: JSON.parse(normalizedAttribute.labels),
          max_file_size: '124.12',
          allowed_extensions: ['png', 'jpg'],
        };
      } else {
        throw new Error(`Attribute of type "${normalizedAttribute.type}" not supported.`);
      }
    });

    this.page.on('request', request => {
      if (
        `http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/attribute` === request.url() &&
        'GET' === request.method()
      ) {
        answerJson(request, attributesSaved);
      }
    });
  });

  Then('there should be the following attributes:', async function(expectedAttributes) {
    await showAttributesTab(this.page);

    const attributes = await await getElement(this.page, 'Attributes');
    const isValid = await expectedAttributes.hashes().reduce(async (isValid, expectedAttribute) => {
      return (await isValid) && (await attributes.hasAttribute(expectedAttribute.code, expectedAttribute.type));
    }, true);
    assert.strictEqual(isValid, true);
  });

  Then('the user edit the attribute {string}', async function(attributeIdentifier) {
    await showAttributesTab(this.page);

    const attributes = await await getElement(this.page, 'Attributes');

    await attributes.edit(attributeIdentifier);
  });

  Then('the list of attributes should be empty', async function() {
    await showAttributesTab(this.page);

    const attributes = await await getElement(this.page, 'Attributes');
    const isEmpty = await attributes.isEmpty();

    assert.strictEqual(isEmpty, true);
  });

  When('the user deletes the attribute {string} linked to the enriched entity {string}', async function(
    attributeIdentifier,
    enrichedEntityIdentifier
  ) {
    await showAttributesTab(this.page);
    const attributes = await await getElement(this.page, 'Attributes');

    this.page.once('request', request => {
      const baseUrl = 'http://pim.com/rest/enriched_entity';
      const deleteUrl = `${baseUrl}/${enrichedEntityIdentifier}/attribute/${attributeIdentifier}`;
      if (deleteUrl === request.url() && 'DELETE' === request.method()) {
        answerJson(request, {}, 204);
      }

      return request;
    });

    await attributes.remove(attributeIdentifier);
  });

  When('the user cancel the deletion of attribute', async function() {
    await showAttributesTab(this.page);
    const attributes = await await getElement(this.page, 'Attributes');
    await attributes.cancelDeletion();
  });

  When('the user cannot deletes the attribute {string} linked to the enriched entity {string}', async function(
    attributeIdentifier,
    enrichedEntityIdentifier
  ) {
    await showAttributesTab(this.page);
    const attributes = await await getElement(this.page, 'Attributes');

    this.page.once('request', request => {
      const baseUrl = 'http://pim.com/rest/enriched_entity';
      const deleteUrl = `${baseUrl}/${enrichedEntityIdentifier}/attribute/${attributeIdentifier}`;
      if (deleteUrl === request.url() && 'DELETE' === request.method()) {
        answerJson(request, {}, 404);
      }

      return request;
    });

    await attributes.remove(attributeIdentifier);
  });

  Then('there should not be the following attributes:', async function(expectedAttributes) {
    const attributes = await await getElement(this.page, 'Attributes');
    const isValid = await expectedAttributes.hashes().reduce(async (isValid, expectedAttribute) => {
      return (await isValid) && !(await attributes.hasAttribute(expectedAttribute.code, expectedAttribute.type));
    }, true);
    assert.strictEqual(isValid, true);
  });
};
