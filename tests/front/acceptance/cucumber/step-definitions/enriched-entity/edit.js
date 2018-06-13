const EnrichedEntityBuilder = require('../../../../common/builder/enriched-entity.js');

const {
    decorators: { createElementDecorator }
} = require('../../test-helpers.js');

module.exports = async function (cucumber) {
  const { Given, Then, When, Before } = cucumber;
  const assert = require('assert');

  const config = {
    'Sidebar': {
        selector: '.AknColumn',
        decorator: require('../../decorators/enriched-entity/sidebar.decorator')
    }
  };

  const getElement = createElementDecorator(config);

  Given('the following configured tabs:', async function(tabs) {
      this.expectedTabs = await tabs.hashes().reduce((previous, current) => {
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
      const sidebar = await (await getElement(this.page, 'Sidebar'));
      await this.page.evaluate(async (sidebar) => {
          const button = await sidebar.getCollapseButton();
          button.click();
      });
  });

  Then('the user should see the sidebar collapsed', async function () {
      await this.page.waitFor('.AknColumn--collapsed');
  });

  Then('the user should see the sidebar with the configured tabs', async function () {
      const sidebar = await (await getElement(this.page, 'Sidebar'));
      debugger;
      const values = await sidebar.getTabsCode();

      assert.deepStrictEqual(values, this.expectedTabs);
  });

    Then('the user should see the properties view', async function () {
        const activeTab = await this.page.evaluate(
            async (getElement) => {
                const sidebar = await(await getElement(this.page, 'Sidebar'));
                return await sidebar.getActiveTabCode();
            }
        );

        await this.page.waitFor(`[data-tab=${activeTab}]`);
    });
};
