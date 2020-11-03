const timeout = 10000;

let page = global.__PAGE__;

// Setup to intercept the calls and return a fake response
beforeEach(async () => {
  page.on('request', interceptedRequest => {
    // Intercept the call to get the product attributes
    if (
      'http://pim.com/rest/attribute/?types%5B%5D=pim_catalog_asset_collection&options%5Blimit%5D=100' ===
        interceptedRequest.url() &&
      'POST' === interceptedRequest.method()
    ) {
      const attributes = [
        {
          code: 'packshot',
          type: 'pim_catalog_asset_collection',
          group: 'marketing',
          reference_data_name: 'packshot',
          labels: {
            en_US: 'Packshot',
          },
          is_read_only: null,
          available_locales: [],
        },
        {
          code: 'notices',
          type: 'pim_catalog_asset_collection',
          group: 'technical',
          reference_data_name: 'notice',
          labels: {
            en_US: 'Notice',
          },
          is_read_only: null,
          available_locales: [],
        },
        {
          code: 'videos',
          type: 'pim_catalog_asset_collection',
          group: 'technical',
          reference_data_name: 'video_presentation',
          labels: {
            en_US: 'Videos',
          },
          is_read_only: null,
          available_locales: ['en_US'],
        },
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
    // Sometimes this test fails on circle ci. This wait should mitigate that
    await new Promise(resolve => setTimeout(resolve, 500));

    const fetchAssetAttributes = require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/attribute')
      .fetchAssetAttributes;

    return await fetchAssetAttributes();
  });

  // Check the attributes returned by the fetcher are the expected ones
  expect(response).toEqual([
    {
      code: 'packshot',
      type: 'pim_catalog_asset_collection',
      group: 'marketing',
      referenceDataName: 'packshot',
      labels: {
        en_US: 'Packshot',
      },
      isReadOnly: null,
      availableLocales: [],
    },
    {
      code: 'notices',
      type: 'pim_catalog_asset_collection',
      group: 'technical',
      referenceDataName: 'notice',
      labels: {
        en_US: 'Notice',
      },
      isReadOnly: null,
      availableLocales: [],
    },
    {
      code: 'videos',
      type: 'pim_catalog_asset_collection',
      group: 'technical',
      referenceDataName: 'video_presentation',
      labels: {
        en_US: 'Videos',
      },
      isReadOnly: null,
      availableLocales: ['en_US'],
    },
  ]);
});
