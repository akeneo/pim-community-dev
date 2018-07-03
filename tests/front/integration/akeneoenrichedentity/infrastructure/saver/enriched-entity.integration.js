const timeout = 5000;

const EnrichedEntityBuilder = require('../../../../common/builder/enriched-entity.js');

describe('Akeneoenrichedentity > infrastructure > saver > enriched-entity', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It saves an enriched entity', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/enriched_entity/sofa' === interceptedRequest.url() &&
        'POST' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createEnrichedEntity = require('akeneoenrichedentity/domain/model/enriched-entity/enriched-entity')
        .createEnrichedEntity;
      const createIdentifier = require('akeneoenrichedentity/domain/model/enriched-entity/identifier').createIdentifier;
      const createLabelCollection = require('akeneoenrichedentity/domain/model/label-collection').createLabelCollection;

      const savedSofa = createEnrichedEntity(
        createIdentifier('sofa'),
        createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'})
      );
      const saver = require('akeneoenrichedentity/infrastructure/saver/enriched-entity').default;

      return await saver.save(savedSofa);
    });

    expect(response).toEqual(undefined);
  });
});
