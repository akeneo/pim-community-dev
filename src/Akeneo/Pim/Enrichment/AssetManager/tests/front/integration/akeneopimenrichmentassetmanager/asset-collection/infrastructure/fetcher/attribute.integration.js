const timeout = 5000;

let page = global.__PAGE__;

// Setup to intercept the calls and return a fake response
beforeEach(async () => {
  page.on('request', interceptedRequest => {
    // Intercept the call to get the product attributes
    if (
      'http://pim.com/rest/attribute/' === interceptedRequest.url() &&
      'POST' === interceptedRequest.method()
    ) {
      const attributes = [{
          code: 'packshot',
          type: 'akeneo_asset_multiple_link',
          group: 'marketing',
          reference_data_name: 'packshot',
          labels: {
            en_US: 'Packshot'
          },
          is_read_only: null
        },
        {
          code: 'notices',
          type: 'akeneo_asset_multiple_link',
          group: 'technical',
          reference_data_name: 'notice',
          labels: {
            en_US: 'Notice'
          },
          is_read_only: null
        },
        {
          code: 'videos',
          type: 'akeneo_asset_multiple_link',
          group: 'technical',
          reference_data_name: 'video_presentation',
          labels: {
            en_US: 'Videos'
          },
          is_read_only: null
        }
      ];

      interceptedRequest.respond({
        contentType: 'application/json',
        body: JSON.stringify(attributes),
      });
    }
  });

  await page.reload();
}, timeout);

it('It fetches all product attributes of asset', async () => {
  // It fetches the product attributes
  const response = await page.evaluate(async () => {
    const fetchAssetAttributes =
      require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/attribute')
      .fetchAssetAttributes;
    const fetcherRegistry = require('pim/fetcher-registry');
    fetcherRegistry.initialize();

    return await fetchAssetAttributes(fetcherRegistry.getFetcher('attribute'))();
  });

  // Check the attributes returned by the fetcher are the expected ones
  expect(response).toEqual([{
      code: 'packshot',
      type: 'akeneo_asset_multiple_link',
      group: 'marketing',
      referenceDataName: 'packshot',
      labels: {
        en_US: 'Packshot'
      },
      isReadOnly: null
    },
    {
      code: 'notices',
      type: 'akeneo_asset_multiple_link',
      group: 'technical',
      referenceDataName: 'notice',
      labels: {
        en_US: 'Notice'
      },
      isReadOnly: null
    },
    {
      code: 'videos',
      type: 'akeneo_asset_multiple_link',
      group: 'technical',
      referenceDataName: 'video_presentation',
      labels: {
        en_US: 'Videos'
      },
      isReadOnly: null
    }
  ]);
});
