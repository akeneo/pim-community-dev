const timeout = 5000;

describe('>>>FETCHER --- product', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
    await page.evaluate(async () => await require('pim/fetcher-registry').initialize());
  }, timeout);

  it('provide an empty list of products', async () => {
    page.once('request', interceptedRequest => {
      interceptedRequest.respond({
        contentType: 'application/json',
        body: '{"items": [], "total": 0}',
      });
    });

    const [err, products] = await page.evaluate(async () => {
      const fetcherRegistry = require('pim/fetcher-registry');

      return await fetcherRegistry
        .getFetcher('product-grid')
        .search({locale: 'en_US', channel: 'ecommerce', limit: 25, page: 0, filters: []});
    });

    expect(products).toEqual({items: [], total: 0});
    expect(err).toBeNull();
  });

  it('provide an error if not authenticated', async () => {
    page.once('request', interceptedRequest => {
      interceptedRequest.respond({
        status: 403,
      });
    });

    const [err, products] = await page.evaluate(async () => {
      const fetcherRegistry = require('pim/fetcher-registry');

      return await fetcherRegistry
        .getFetcher('product-grid')
        .search({locale: 'en_US', channel: 'ecommerce', limit: 25, page: 0, filters: []});
    });

    expect(products).toBeNull();
    expect(err.status).toEqual(403);
  });
});
