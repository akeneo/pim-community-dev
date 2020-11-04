const timeout = 10000;

let page = global.__PAGE__;

// Setup to intercept the calls and return a fake response
beforeEach(async () => {
  page.on('request', interceptedRequest => {
    const baseUrl = interceptedRequest.url().split('?')[0];
    // Intercept the call to get the product attribute groups
    if ('http://pim.com/rest/attribute-group/' === baseUrl && 'GET' === interceptedRequest.method()) {
      const attributeGroups = {
        marketing: {
          code: 'marketing',
          sort_order: 1,
        },
        technical: {
          code: 'technical',
          sort_order: 2,
        },
        design: {
          code: 'design',
          sort_order: 3,
        },
        manufacturing: {
          code: 'manufacturing',
          sort_order: 4,
        },
        color: {
          code: 'color',
          sort_order: 5,
        },
        size: {
          code: 'size',
          sort_order: 6,
        },
        medias: {
          code: 'medias',
          sort_order: 7,
        },
        erp: {
          code: 'erp',
          sort_order: 8,
        },
        ecommerce: {
          code: 'ecommerce',
          sort_order: 9,
        },
        product: {
          code: 'product',
          sort_order: 10,
        },
        other: {
          code: 'other',
          sort_order: 100,
        },
      };

      interceptedRequest.respond({
        contentType: 'application/json',
        body: JSON.stringify(attributeGroups),
      });
    }
  });

  await page.reload();
}, timeout);

it('It fetches all product attribute groups of asset', async () => {
  // It fetches the product attribute groups
  const response = await page.evaluate(async () => {
    // Sometimes this test fails on circle ci. This wait should mitigate that
    await new Promise(resolve => setTimeout(resolve, 500));

    const fetchAssetAttributeGroups = require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/attribute-group')
      .fetchAssetAttributeGroups;
    const fetcherRegistry = require('pim/fetcher-registry');
    fetcherRegistry.initialize();

    return await fetchAssetAttributeGroups(fetcherRegistry.getFetcher('attribute-group'))();
  });

  // Check the attributes returned by the fetcher are the expected ones
  expect(response).toEqual({
    marketing: {
      code: 'marketing',
      sort_order: 1,
    },
    technical: {
      code: 'technical',
      sort_order: 2,
    },
    design: {
      code: 'design',
      sort_order: 3,
    },
    manufacturing: {
      code: 'manufacturing',
      sort_order: 4,
    },
    color: {
      code: 'color',
      sort_order: 5,
    },
    size: {
      code: 'size',
      sort_order: 6,
    },
    medias: {
      code: 'medias',
      sort_order: 7,
    },
    erp: {
      code: 'erp',
      sort_order: 8,
    },
    ecommerce: {
      code: 'ecommerce',
      sort_order: 9,
    },
    product: {
      code: 'product',
      sort_order: 10,
    },
    other: {
      code: 'other',
      sort_order: 100,
    },
  });
});
