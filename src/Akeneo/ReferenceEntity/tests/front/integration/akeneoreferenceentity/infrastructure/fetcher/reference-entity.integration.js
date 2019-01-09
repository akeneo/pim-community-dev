const timeout = 5000;

const ReferenceEntityBuilder = require('../../../../common/builder/reference-entity.js');

describe('Akeneoreferenceentity > infrastructure > fetcher > reference-entity', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It search for reference entities', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/reference_entity' === interceptedRequest.url() &&
        'GET' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify({
            items: [],
          }),
        });
      }
    });

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoreferenceentity/infrastructure/fetcher/reference-entity').default;

      return await fetcher.search();
    });

    expect(response).toEqual({
      items: [],
    });
  });

  it('It fetches one reference entity', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/reference_entity/sofa' === interceptedRequest.url() &&
        'GET' === interceptedRequest.method()
      ) {
        const referenceEntity = new ReferenceEntityBuilder()
          .withIdentifier('sofa')
          .withLabels({
            en_US: 'Sofa',
            fr_FR: 'Canapé',
          })
          .withImage({
            filePath: '/path/sofa.jpg',
            originalFilename: 'sofa.jpg',
          })
          .withAttributes([])
          .build();

        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify({...referenceEntity, record_count: 123}),
        });
      }
    });

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoreferenceentity/infrastructure/fetcher/reference-entity').default;
      const identifierModule = 'akeneoreferenceentity/domain/model/reference-entity/identifier';
      const referenceEntityIdentifier = require(identifierModule).createIdentifier('sofa');

      return await fetcher.fetch(referenceEntityIdentifier);
    });

    expect(response).toEqual({
      attributes: [],
      recordCount: 123,
      referenceEntity: {
        identifier: {
          identifier: 'sofa',
        },
        labelCollection: {
          labels: {
            en_US: 'Sofa',
            fr_FR: 'Canapé',
          },
        },
        image: {
          filePath: '/path/sofa.jpg',
          originalFilename: 'sofa.jpg',
        },
      },
    });
  });
});
