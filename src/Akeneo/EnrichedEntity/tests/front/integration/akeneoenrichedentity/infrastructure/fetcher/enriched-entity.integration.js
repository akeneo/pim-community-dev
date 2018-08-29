const timeout = 5000;

const EnrichedEntityBuilder = require('../../../../common/builder/enriched-entity.js');

describe('Akeneoenrichedentity > infrastructure > fetcher > enriched-entity', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It search for enriched entities', async () => {
    page.on('request', interceptedRequest => {
      if ('http://pim.com/rest/enriched_entity' === interceptedRequest.url() && 'GET' === interceptedRequest.method()) {
        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify({
            items: [],
            total: 0
          })
        });
      }
    });

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoenrichedentity/infrastructure/fetcher/enriched-entity').default;

      return await fetcher.search();
    });

    expect(response).toEqual({
      items: [],
      total: 0
    });
  });

  it('It fetches one enriched entity', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/enriched_entity/sofa' === interceptedRequest.url() &&
        'GET' === interceptedRequest.method()
      ) {
        const enrichedEntity = new EnrichedEntityBuilder()
          .withIdentifier('sofa')
          .withLabels({
            en_US: 'Sofa',
            fr_FR: 'Canapé'
          })
          .withImage({
            'filePath': '/path/sofa.jpg',
            'originalFilename': 'sofa.jpg'
          })
          .build();

        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify(enrichedEntity)
        });
      }
    });

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoenrichedentity/infrastructure/fetcher/enriched-entity').default;

      return await fetcher.fetch('sofa');
    });

    expect(response).toEqual({
      identifier: {
        identifier: 'sofa'
      },
      labelCollection: {
        labels: {
          en_US: 'Sofa',
          fr_FR: 'Canapé'
        }
      },
      image: {
        filePath: '/path/sofa.jpg',
        originalFilename: 'sofa.jpg'
      },
    });
  });
});
