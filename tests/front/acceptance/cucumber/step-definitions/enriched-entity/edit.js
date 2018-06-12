const EnrichedEntityBuilder = require('../../../../common/builder/enriched-entity.js');

const {
    tools: { answerJson }
} = require('../../test-helpers.js');

module.exports = async function (cucumber) {
  const { Given, Then, When } = cucumber;
  const assert = require('assert');

  When('the user asks for the enriched entity {string}', async function (identifier) {
    await this.page.evaluate(async (identifier) => {
        const Controller = require('pim/controller/enriched-entity/edit');
        const controller = new Controller();
        controller.renderRoute({params: { identifier }});
        await document.getElementById('app').appendChild(controller.el);
    }, identifier);

    await this.page.waitFor('.object-attributes');
  });

  Given('the user gets the enriched entity {string} with label {string}', async function (
    expectedIdentifier,
    expectedLabel
  ) {
    await this.page.waitForSelector('.AknTextField[name="identifier"]');
    const identifier = await this.page.$('.AknTextField[name="identifier"]');
    const identifierProperty = await identifier.getProperty('value');
    const identifierValue = await identifierProperty.jsonValue();
    assert.equal(identifierValue, expectedIdentifier);

    await this.page.waitForSelector('.AknTextField[name="label"]');
    const label = await this.page.$('.AknTextField[name="label"]');
    const labelProperty = await label.getProperty('value');
    const labelValue = await labelProperty.jsonValue();
    assert.equal(labelValue, expectedLabel);
  });
};
