const EnrichedEntityBuilder = require('../../../../common/builder/enriched-entity.js');
const Grid = require('../../decorators/enriched-entity/index/grid.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
  tools: {answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {Given, Then, When} = cucumber;
  const assert = require('assert');

  const config = {
    Grid: {
      selector: '.AknGridContainer',
      decorator: Grid,
    },
  };

  const getElement = createElementDecorator(config);

  const givenEnrichedEntities = function(enrichedEntities) {
    const enrichedEntityResponse = enrichedEntities.hashes().map(function(enrichedEntity) {
      const enrichedEntityBuilder = new EnrichedEntityBuilder();

      if (undefined !== enrichedEntity.identifier) {
        enrichedEntityBuilder.withIdentifier(enrichedEntity.identifier);
      }
      if (undefined !== enrichedEntity.labels) {
        enrichedEntityBuilder.withLabels(JSON.parse(enrichedEntity.labels));
      }
      if (undefined !== enrichedEntity.image) {
        enrichedEntityBuilder.withImage(JSON.parse(enrichedEntity.image));
      } else {
        enrichedEntityBuilder.withImage(null);
      }

      return enrichedEntityBuilder.build();
    });

    enrichedEntityResponse.forEach(enrichedEntity => {
      this.page.on('request', request => {
        if (
          `http://pim.com/rest/enriched_entity/${enrichedEntity.identifier}` === request.url() &&
          'GET' === request.method()
        ) {
          answerJson(request, enrichedEntity);
        }
      });
    });

    this.page.on('request', request => {
      if ('http://pim.com/rest/enriched_entity' === request.url()) {
        answerJson(request, {items: enrichedEntityResponse, total: 1000});
      }
    });
  };
  Given('the following enriched entities to list:', givenEnrichedEntities);
  Given('the following enriched entities to show:', givenEnrichedEntities);
  Given('the following enriched entity:', givenEnrichedEntities);

  When('the user asks for the enriched entity list', async function() {
    await this.page.evaluate(async () => {
      const Controller = require('pim/controller/enriched-entity/list');
      const controller = new Controller();
      controller.renderRoute();
      await document.getElementById('app').appendChild(controller.el);
    });

    const grid = await await getElement(this.page, 'Grid');
    const isLoaded = await grid.isLoaded();

    assert.equal(isLoaded, true);
  });

  Then('the user gets a selection of {int} items out of {int} items in total', async function(count, total) {
    const grid = await await getElement(this.page, 'Grid');
    const rows = await grid.getRowsAfterLoading();
    assert.equal(rows.length, count);

    const title = await grid.getTitle();
    assert.equal(title.trim(), `${total} result${total > 1 ? 's' : ''}`);
  });

  Then('the user gets an enriched entity {string}', async function(identifier) {
    const grid = await await getElement(this.page, 'Grid');
    await grid.hasRow(identifier);
  });

  Then('there is no enriched entity', async function() {
    const grid = await await getElement(this.page, 'Grid');
    const rows = await grid.getRows();
    assert.equal(rows.length, 0);
  });

  Then('the user asks for the next enriched entities', async function() {
    this.page.evaluate(() => {
      window.scrollBy(0, window.innerHeight);
    });
  });
};
