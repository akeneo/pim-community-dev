const Edit = require('../../decorators/enriched-entity/edit.decorator');
const EnrichedEntityBuilder = require('../../../../common/builder/enriched-entity.js');

const {
  decorators: {createElementDecorator},
  tools: {convertDataTable, convertItemTable},
} = require('../../test-helpers.js');

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
    },
  };

  const getElement = createElementDecorator(config);

  const askForEnrichedEntity = async function(identifier) {
    await this.page.evaluate(async identifier => {
      const Controller = require('pim/controller/enriched-entity/edit');
      const controller = new Controller();
      controller.renderRoute({params: {identifier}});
      await document.getElementById('app').appendChild(controller.el);
    }, identifier);

    await this.page.waitFor('.object-attributes');
    const editPage = await (await getElement(this.page, 'Edit'));
    const properties = await editPage.getProperties();
    const isLoaded = await properties.isLoaded();

    assert.equal(isLoaded, true);
  };

  const changeEnrichedEntity = async function (editPage, identifier, updates) {
    const properties = await editPage.getProperties();

    // To rework when we will be able to switch locale
    const labels = convertDataTable(updates).labels;

    await properties.setLabel(labels.en_US);
  }

  const savedEnrichedEntityWillBe = async function (page, identifier, updates) {
    const labels = convertDataTable(updates).labels;
    const enrichedEntityBuilder = new EnrichedEntityBuilder();
    enrichedEntityBuilder.withIdentifier(identifier);
    enrichedEntityBuilder.withLabels(labels);

    const enrichedEntity = enrichedEntityBuilder.build();

    page.on('request', request => {
      if (`http://pim.com/rest/enriched_entity/${identifier}` === request.url()
        && 'POST' === request.method()) {
        answerJson(request, enrichedEntity);
      }
    });
  }

  When('the user asks for the enriched entity {string}', askForEnrichedEntity);

  When('the user gets the enriched entity {string} with label {string}', async function(
    expectedIdentifier,
    expectedLabel
  ) {
    const editPage = await (await getElement(this.page, 'Edit'));
    const properties = await editPage.getProperties();
    const identifierValue = await properties.getIdentifier();
    assert.equal(identifierValue, expectedIdentifier);

    const labelValue = await properties.getLabel();
    assert.equal(labelValue, expectedLabel);
  });

  When('the user updates the enriched entity {string} with:', async function(identifier, updates) {
    await askForEnrichedEntity.apply(this, [identifier]);

    const editPage = await (await getElement(this.page, 'Edit'));
    await changeEnrichedEntity(editPage, identifier, updates);
    await savedEnrichedEntityWillBe(this.page, identifier, updates);
    await editPage.save();
  });

  When('the user changes the enriched entity {string} with:', async function(identifier, updates) {
    await askForEnrichedEntity.apply(this, [identifier]);

    const editPage = await (await getElement(this.page, 'Edit'));

    await changeEnrichedEntity.apply(this, [editPage, identifier, updates]);
  });


  Then('the enriched entity {string} should be:', async function(identifier, updates) {
    const enrichedEntity = convertItemTable(updates)[0];

    const editPage = await (await getElement(this.page, 'Edit'));
    const properties = await editPage.getProperties();
    const identifierValue = await properties.getIdentifier();
    assert.equal(identifierValue, enrichedEntity.identifier);

    const labelValue = await properties.getLabel();
    // To rework when we will be able to switch locale
    assert.equal(labelValue, enrichedEntity.labels['en_US']);
  });

  Then('the saved enriched entity {string} will be:', function (identifier, updates) {
    savedEnrichedEntityWillBe(this.page, identifier, updates)
  });

  Then('the user saves the changes', async function () {
    const editPage = await (await getElement(this.page, 'Edit'));
    await editPage.save();
  });
};
