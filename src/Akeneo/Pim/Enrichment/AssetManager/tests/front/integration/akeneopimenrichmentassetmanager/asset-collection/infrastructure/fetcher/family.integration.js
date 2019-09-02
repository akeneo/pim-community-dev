const timeout = 5000;

let page = global.__PAGE__;

beforeEach(async () => {
  await page.reload();
}, timeout);

it('It fetches the family', async () => {
  page.on('request', interceptedRequest => {
    if (
      'http://pim.com/configuration/family/rest/scanners' === interceptedRequest.url() &&
      'GET' === interceptedRequest.method()
    ) {
      const family = {
        code: 'scanners',
        attribute_requirements: {
          ecommerce: [
            'color_scanning',
            'description',
            'name',
            'notices',
            'packshot',
            'price',
            'sku'
          ]
        }
      };

      interceptedRequest.respond({
        contentType: 'application/json',
        body: JSON.stringify(family),
      });
    }
  });

  const response = await page.evaluate(async () => {
    const fetchFamily =
      require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/family')
      .fetchFamily;
    const fetcherRegistry = require('pim/fetcher-registry');
    fetcherRegistry.initialize();
    const family = await fetchFamily(fetcherRegistry.getFetcher('family'))('scanners');

    return family;
  });

  expect(response).toEqual({
    code: 'scanners',
    attributeRequirements: {
      ecommerce: [
        'color_scanning',
        'description',
        'name',
        'notices',
        'packshot',
        'price',
        'sku'
      ]
    }
  });
});
