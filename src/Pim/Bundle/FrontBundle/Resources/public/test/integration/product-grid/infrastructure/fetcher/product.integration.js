const timeout = 5000;

describe('>>>FETCHER --- product', () => {
  let page = global.__PAGE__;
  beforeAll(async () => {
    await page.goto('http://pim.com');

    await page.evaluate(async () => await require('pim/fetcher-registry').initialize());
  }, timeout);

  it('provide an empty list of products', async () => {
    await page.setRequestInterception(true);
    page.on('request', interceptedRequest => {
      interceptedRequest.respond({
        contentType: 'application/json',
        body: '{"items": [], "total": 0}'
      })
    });

    const products = await page.evaluate(async () => {
      return await require('pim/fetcher-registry')
        .getFetcher('product-grid')
        .search({"locale":"en_US","channel":"ecommerce","limit":25,"page":0,"filters":[]});
    });

    expect(products).toEqual({items: [], total: 0});
  });

  afterAll(async () => {
    await page.close()
  });
});
