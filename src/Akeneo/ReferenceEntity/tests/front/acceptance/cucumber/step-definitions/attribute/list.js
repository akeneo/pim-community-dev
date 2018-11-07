const path = require('path');
const Sidebar = require('../../decorators/reference-entity/app/sidebar.decorator');
const Attributes = require('../../decorators/reference-entity/edit/attributes.decorator');
const {getRequestContract, listenRequest} = require('../../tools');

const {
  decorators: {createElementDecorator},
  tools: {answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));
const attributeIdentifierSuffix = '123456';

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
    await sidebar.clickOnTab('attribute');
  };

  Given('the following attributes for the reference entity {string}:', async function(
    referenceEntityIdentifier,
    attributes
  ) {
    const attributesSaved = attributes.hashes().map(normalizedAttribute => {
      if ('text' === normalizedAttribute.type) {
        return {
          identifier: `${referenceEntityIdentifier}_${normalizedAttribute.code}_${attributeIdentifierSuffix}`,
          reference_entity_identifier: referenceEntityIdentifier,
          code: normalizedAttribute.code,
          is_required: false,
          order: 0,
          value_per_locale: true,
          value_per_channel: false,
          type: 'text',
          labels: JSON.parse(normalizedAttribute.labels),
          max_length: 255,
          is_textarea: false,
          is_rich_text_editor: false,
          validation_rule: 'none',
          regular_expression: null,
        };
      } else if ('image' === normalizedAttribute.type) {
        return {
          identifier: `${referenceEntityIdentifier}_${normalizedAttribute.code}_${attributeIdentifierSuffix}`,
          reference_entity_identifier: referenceEntityIdentifier,
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
        `http://pim.com/rest/reference_entity/${referenceEntityIdentifier}/attribute` === request.url() &&
        'GET' === request.method()
      ) {
        answerJson(request, attributesSaved);
      }
    });
  });

  Then('there should be the following attributes:', async function(expectedAttributes) {
    await showAttributesTab(this.page);

    const attributes = await await getElement(this.page, 'Attributes');
    const hasAllAttribute = await expectedAttributes.hashes().reduce(async (hasAllAttribute, expectedAttribute) => {
      return (await hasAllAttribute) && (await attributes.hasAttribute(expectedAttribute.code, expectedAttribute.type));
    }, true);
    assert.strictEqual(hasAllAttribute, true);
  });

  const editAttribute = async function(page, attributeIdentifier) {
    await showAttributesTab(page);

    const attributes = await await getElement(page, 'Attributes');

    await attributes.edit(attributeIdentifier);
  };

  Then('the user edit the attribute {string}', async function(attributeIdentifier) {
    await editAttribute(this.page, attributeIdentifier);
  });

  Then('the list of attributes should be empty', async function() {
    await showAttributesTab(this.page);

    const attributes = await await getElement(this.page, 'Attributes');
    const isEmpty = await attributes.isEmpty();

    assert.strictEqual(isEmpty, true);
  });

  When('the user deletes the attribute {string} linked to the reference entity {string}', async function(
    attributeIdentifier,
    referenceEntityIdentifier
  ) {
    await showAttributesTab(this.page);
    const attributes = await await getElement(this.page, 'Attributes');

    await editAttribute(this.page, attributeIdentifier);
    this.page.on('request', request => {
      const baseUrl = 'http://pim.com/rest/reference_entity';
      const identifier = `${referenceEntityIdentifier}_${attributeIdentifier}_${attributeIdentifierSuffix}`;
      const deleteUrl = `${baseUrl}/${referenceEntityIdentifier}/attribute/${identifier}`;
      if (deleteUrl === request.url() && 'DELETE' === request.method()) {
        answerJson(request, {}, 204);
      }

      return request;
    });

    await attributes.remove(attributeIdentifier);
  });

  When('the user cancel the deletion of attribute {string}', async function(attributeIdentifier) {
    await showAttributesTab(this.page);
    const attributes = await await getElement(this.page, 'Attributes');
    await editAttribute(this.page, attributeIdentifier);

    await attributes.cancelDeletion();
  });

  When('the user cannot deletes the attribute {string} linked to the reference entity {string}', async function(
    attributeIdentifier,
    referenceEntityIdentifier
  ) {
    await showAttributesTab(this.page);
    const attributes = await await getElement(this.page, 'Attributes');

    await editAttribute(this.page, attributeIdentifier);

    this.page.on('request', request => {
      const baseUrl = 'http://pim.com/rest/reference_entity';
      const identifier = `${referenceEntityIdentifier}_${attributeIdentifier}_${attributeIdentifierSuffix}`;
      const deleteUrl = `${baseUrl}/${referenceEntityIdentifier}/attribute/${identifier}`;
      if (deleteUrl === request.url() && 'DELETE' === request.method()) {
        answerJson(request, {}, 404);
      }

      return request;
    });

    const requestContract = getRequestContract('Attribute/ListDetails/ok/name_portrait.json');

    await listenRequest(this.page, requestContract);

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
