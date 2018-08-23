const path = require('path');
const Attributes = require('../../decorators/enriched-entity/edit/attributes.decorator');
const Edit = require('../../decorators/enriched-entity/edit.decorator');
const Sidebar = require('../../decorators/enriched-entity/app/sidebar.decorator');

const {
  decorators: {createElementDecorator},
  tools: {answerJson}
} = require(path.resolve(
    process.cwd(),
    './tests/front/acceptance/cucumber/test-helpers.js'
));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Sidebar: {
      selector: '.AknColumn',
      decorator: Sidebar
    },
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
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

  When('the user deletes the attribute {string} linked to the enriched entity {string}',
    async function (attributeIdentifier, enrichedEntityIdentifier) {
      await showAttributesTab(this.page);
      const attributes = await await getElement(this.page, 'Attributes');

      this.page.once('request', (request) => {
        const deleteUrl =
          `http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/attribute/${attributeIdentifier}`;
        if (deleteUrl === request.url() && 'DELETE' === request.method()) {
          answerJson(request, {}, 204);
        }

        return request;
      });

      await attributes.remove();
    }
  );

  When('the user cancel the deletion of attribute',
    async function () {
      await showAttributesTab(this.page);
      const attributes = await await getElement(this.page, 'Attributes');
      await attributes.cancelDeletion();
    }
  );

  When('the user cannot deletes the attribute {string} linked to the enriched entity {string}',
    async function (attributeIdentifier, enrichedEntityIdentifier) {
      await showAttributesTab(this.page);
      const attributes = await await getElement(this.page, 'Attributes');

      this.page.once('request', (request) => {
        const deleteUrl =
          `http://pim.com/rest/enriched_entity/${enrichedEntityIdentifier}/attribute/${attributeIdentifier}`;
        if (deleteUrl === request.url() && 'DELETE' === request.method()) {
          answerJson(request, {}, 404);
        }

        return request;
      });

      await attributes.remove();
    }
  );

  Then('the user should not see the delete notification', async function() {
    const editPage = await await getElement(this.page, 'Edit');
    const hasNoNotification = await editPage.hasNoNotification();

    assert.strictEqual(hasNoNotification, true);
  });

  Then('there should not be the following attributes:', async function (expectedAttributes) {
    const attributes = await await getElement(this.page, 'Attributes');
    const isValid = await expectedAttributes.hashes().reduce(async (isValid, expectedAttribute) => {
      return await isValid && !await attributes.hasAttribute(expectedAttribute.code, expectedAttribute.type);
    }, true);
    assert.strictEqual(isValid, true);
  });
};
