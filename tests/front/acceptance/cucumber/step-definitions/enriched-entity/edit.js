const EnrichedEntityBuilder = require('../../../../common/builder/enriched-entity.js');

const {
    tools: { answerJson }
} = require('../../test-helpers.js');

module.exports = async function (cucumber) {
  const { Given, Then, When } = cucumber;
  const assert = require('assert');

  Given('the following configured tabs:', async function(tabs) {
      this.expectedTabs = tabs.hashes().reduce((previous, current) => {
          return [...previous, current.code];
      }, []);
  });

  When('the user asks for the enriched entity {string}', async function (identifier) {
    await this.page.evaluate(async (identifier) => {
        const Controller = require('pim/controller/enriched-entity/edit');
        const controller = new Controller();
        controller.renderRoute({params: { identifier }});
        await document.getElementById('app').appendChild(controller.el);
    }, identifier);

    await this.page.waitFor('.object-attributes');
  });

  When('the user gets the enriched entity {string} with label {string}', async function (
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

  When('the user tries to collapse the sidebar', async function () {
      await this.page.evaluate(async () => {
          const element = document.querySelector('.AknColumn-collapseButton');
          element.click();
      });
  });

  Then('the user should see the sidebar collapsed', async function () {
      await this.page.waitFor('.AknColumn--collapsed:defined');
  });

  Then('the user should see the sidebar with the configured tabs', async function () {
      const values = await this.page.evaluate(
          () => [...document.querySelectorAll('.AknColumn-navigationLink')]
              .map(element => element.getAttribute('data-tab'))
      );

      assert.deepStrictEqual(values, this.expectedTabs);
  });

    Then('the user should see the properties view', async function () {
        const activeTab = await this.page.evaluate(
            () => document.querySelector('.AknColumn-navigationLink--active').getAttribute('data-tab')
        );

        await this.page.waitFor(`#${activeTab}`);
    });
};
