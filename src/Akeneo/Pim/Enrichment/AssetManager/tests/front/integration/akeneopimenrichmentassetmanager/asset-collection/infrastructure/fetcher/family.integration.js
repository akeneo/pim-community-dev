const timeout = 10000;

let page = global.__PAGE__;

// Setup to intercept the calls and return a fake response
beforeEach(async () => {
  page.on('request', interceptedRequest => {
    // Intercept the call to get the family
    if (
      'http://pim.com/configuration/rest/family/scanners' === interceptedRequest.url() &&
      'GET' === interceptedRequest.method()
    ) {
      const family = {
        code: 'scanners',
        labels: {
          en_US: 'Scanners',
        },
        attribute_requirements: {
          ecommerce: ['color_scanning', 'description', 'name', 'notices', 'packshot', 'price', 'sku'],
        },
      };

      interceptedRequest.respond({
        contentType: 'application/json',
        body: JSON.stringify(family),
      });
    }
  });

  await page.reload();
}, timeout);

it('It fetches the family', async () => {
  // It fetches a family
  const response = await page.evaluate(async () => {
    // Sometimes this test fails on circle ci. This wait should mitigate that
    await new Promise(resolve => setTimeout(resolve, 500));

    const fetchFamily = require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/family')
      .fetchFamily;
    const fetcherRegistry = require('pim/fetcher-registry');
    fetcherRegistry.initialize();

    return await fetchFamily(fetcherRegistry.getFetcher('family'))('scanners');
  });

  // Check the family returned by the fetcher is the expected one
  expect(response).toEqual({
    code: 'scanners',
    labels: {
      en_US: 'Scanners',
    },
    attributeRequirements: {
      ecommerce: ['color_scanning', 'description', 'name', 'notices', 'packshot', 'price', 'sku'],
    },
  });
});
