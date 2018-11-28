const ReferenceEntityBuilder = require('../../../../common/builder/reference-entity.js');
const Grid = require('../../decorators/reference-entity/index/grid.decorator');
const path = require('path');
const {getRequestContract, listenRequest} = require('../../tools');

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

  const givenReferenceEntities = function(referenceEntities) {
    const referenceEntityResponse = referenceEntities.hashes().map(function(referenceEntity) {
      const referenceEntityBuilder = new ReferenceEntityBuilder();

      if (undefined !== referenceEntity.identifier) {
        referenceEntityBuilder.withIdentifier(referenceEntity.identifier);
      }
      if (undefined !== referenceEntity.labels) {
        referenceEntityBuilder.withLabels(JSON.parse(referenceEntity.labels));
      }
      if (undefined !== referenceEntity.image) {
        referenceEntityBuilder.withImage(JSON.parse(referenceEntity.image));
      } else {
        referenceEntityBuilder.withImage(null);
      }
      referenceEntityBuilder.withAttributes([]);

      return referenceEntityBuilder.build();
    });

    referenceEntityResponse.forEach(referenceEntity => {
      const answerReferenceEntityRequest = request => {
        if (
          `http://pim.com/rest/reference_entity/${referenceEntity.identifier}` === request.url() &&
          'GET' === request.method()
        ) {
          this.page.removeListener('request', answerReferenceEntityRequest);
          answerJson(request, referenceEntity);
        }
      };
      this.page.on('request', answerReferenceEntityRequest);
    });

    this.page.on('request', request => {
      if ('http://pim.com/rest/reference_entity' === request.url()) {
        answerJson(request, {items: referenceEntityResponse, total: 1000});
      }
    });
  };

  const givenValidReferenceEntity = async function() {
    const requestContract = getRequestContract('ReferenceEntity/ReferenceEntityDetails/ok.json');

    await listenRequest(this.page, requestContract);
  };

  const givenValidBrandReferenceEntity = async function() {
    const requestContract = getRequestContract('ReferenceEntity/ReferenceEntityDetails/brand_ok.json');

    await listenRequest(this.page, requestContract);
  };

  Given('the following reference entities to list:', givenReferenceEntities);
  Given('the following reference entities to show:', givenReferenceEntities);
  Given('a valid reference entity', givenValidReferenceEntity);
  Given('a valid brand reference entity', givenValidBrandReferenceEntity);
  When('the user asks for the reference entity list', async function() {
    await this.page.evaluate(async () => {
      const Controller = require('pim/controller/reference-entity/list');
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
    assert.equal(title.trim(), `${total} Reference Entit${total > 1 ? 'ies' : 'y'}`);
  });

  Then('the user gets a reference entity {string}', async function(identifier) {
    const grid = await await getElement(this.page, 'Grid');
    await grid.hasRow(identifier);
  });

  Then('there is no reference entity', async function() {
    const grid = await await getElement(this.page, 'Grid');
    const rows = await grid.getRows();
    assert.equal(rows.length, 0);
  });

  Then('the user asks for the next reference entities', async function() {
    this.page.evaluate(() => {
      window.scrollBy(0, window.innerHeight);
    });
  });
};
