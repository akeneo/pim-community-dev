const timeout = 5000;

describe('Akeneoenrichedentity > infrastructure > fetcher > enriched-entity', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It search for enriched entities', async () => {
    page.on('request', interceptedRequest => {
      if (interceptedRequest.url().includes('/rest/enriched_entity') &&
        'GET' === interceptedRequest.method()
      ) {
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
});
