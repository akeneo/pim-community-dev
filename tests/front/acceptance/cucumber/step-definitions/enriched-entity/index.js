const EnrichedEntityBuilder = require('../../../../common/builder/enriched-entity.js');

const {
    tools: { answerJson }
} = require('../../test-helpers.js');

module.exports = async function (cucumber) {
  const { Given, Then, When } = cucumber;
  const assert = require('assert');

  Given('the following enriched entities:', function (enrichedEntities) {
    const enrichedEntityResponse = enrichedEntities.hashes().map(function (enrichedEntity) {
      const enrichedEntityBuilder = new EnrichedEntityBuilder();

      if (undefined !== enrichedEntity.identifier) {
        enrichedEntityBuilder.withIdentifier(enrichedEntity.identifier);
      }
      if (undefined !== enrichedEntity.labels) {
        enrichedEntityBuilder.withLabels(JSON.parse(enrichedEntity.labels));
      }

      return enrichedEntityBuilder.build();
    });

    enrichedEntityResponse.forEach(enrichedEntity => {
      this.page.on('request', request => {
        if (`http://pim.com//rest/enriched_entity/${enrichedEntity.identifier}` === request.url()) {
          answerJson(request, enrichedEntity);
        }
      });
    })

    this.page.on('request', request => {
      if ('http://pim.com//rest/enriched_entity' === request.url()) {
        answerJson(request, { items: enrichedEntityResponse, total: 1000 });
      }
    });
  });

  When('the user ask for the enriched entity list', async function () {
    await this.page.evaluate(async () => {
      const Controller = require('pim/controller/enriched-entity/list');
      const controller = new Controller();
      controller.renderRoute();
      await document.getElementById('app').appendChild(controller.el);
    });

    await this.page.waitFor('.AknGridContainer');
  });

  Then('the user get a selection of {int} items out of {int} items in total', async function (count, total) {
    await this.page.waitForSelector('.AknGrid-bodyRow');
    const rows = await this.page.$$('.AknGrid-bodyRow');
    assert.equal(rows.length, count);

    const titleElement = await this.page.waitForSelector('.AknTitleContainer-title');
    const title = await (await titleElement.getProperty('textContent')).jsonValue();

    assert.equal(title.trim(), `${total} result${total > 1 ? 's' : ''}`);
  });

  Then('I get an enriched entity {string}', async function (identifier) {
    await this.page.waitForSelector(`.AknGrid-bodyRow[data-identifier="${identifier}"]`);
  });

  Then('there is no enriched entity', async function () {
    const rows = await this.page.$$('.AknGrid-bodyRow');
    assert.equal(rows.length, 0);
  });

  Then('the user ask for the next enriched entities', async function () {
    this.page.evaluate(_ => {
      window.scrollBy(0, window.innerHeight);
    });
  });
};
