const timeout = 5000;

const EnrichedEntityBuilder = require('../../../../common/builder/enriched-entity.js');

describe('Akeneoenrichedentity > infrastructure > fetcher > record', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It search for records', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/enriched_entity/designer/record' === interceptedRequest.url() &&
        'GET' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify({
            items: [],
            total: 0,
          }),
        });
      }
    });

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoenrichedentity/infrastructure/fetcher/record').default;

      return await fetcher.search({
        filters: [{value: 'designer'}],
      });
    });

    expect(response).toEqual({
      items: [],
      total: 0,
    });
  });
});
