const timeout = 5000;

const AssetFamilyBuilder = require(
  '../../../../../../../../../../AssetManager/tests/front/common/builder/asset-family.js');
const AssetBuilder = require('../../../../../../../../../../AssetManager/tests/front/common/builder/asset.js');

let page = global.__PAGE__;

// Setup to intercept the calls and return a fake response
beforeEach(async () => {
  page.on('request', interceptedRequest => {
    // Intercept the call to get the packshot asset family
    if (
      'http://pim.com/rest/asset_manager/packshot' === interceptedRequest.url() &&
      'GET' === interceptedRequest.method()
    ) {
      const assetFamily = new AssetFamilyBuilder()
        .withIdentifier('packshot')
        .withLabels({
          en_US: 'Packshot',
        })
        .withAttributes([])
        .withAttributeAsImage('')
        .withAttributeAsLabel('')
        .build();

      interceptedRequest.respond({
        contentType: 'application/json',
        body: JSON.stringify(assetFamily),
      });
    }
    // Intercept the call to get the assets of packshot
    if (
      'http://pim.com/rest/asset_manager/packshot/asset' === interceptedRequest.url() &&
      'PUT' === interceptedRequest.method()
    ) {
      const asset = new AssetBuilder()
        .withCode('iphone')
        .withLabels({
          en_US: 'Iphone',
        })
        .withCompleteness(2, 3)
        .withImage('/path/iphone.jpg')
        .build();
      interceptedRequest.respond({
        contentType: 'application/json',
        body: JSON.stringify({
          items: [asset],
          match_count: 1,
          total_count: 1
        }),
      });
    }
  });

  await page.reload();
}, timeout);

it('It fetches the asset collection', async () => {
  // It fetches an asset collection of packshot
  const response = await page.evaluate(async () => {
    const fetchAssetCollection =
      require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/asset')
      .fetchAssetCollection;

return await fetchAssetCollection('packshot', ['iphone'], {
      channel: 'ecommerce',
      locale: 'en_US'
    });
  });

  // Check the asset collection returned by the fetcher is the one expected
  expect(response).toEqual([{
    assetFamily: {
      attributeAsImage: '',
      attributeAsLabel: '',
      code: 'packshot',
      identifier: 'packshot',
      image: null,
      labels: {
        en_US: 'Packshot'
      }
    },
    asset_family_identifier: '',
    code: 'iphone',
    completeness: {
      complete: 2,
      required: 3
    },
    identifier: 'iphone_123456',
    image: '/path/iphone.jpg',
    labels: {
      en_US: 'Iphone'
    }
  }]);
});
