const timeout = 5000;

const AssetFamilyBuilder = require('../../../../common/builder/asset-family.js');

describe('Akeneoassetfamily > infrastructure > fetcher > asset-family', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It search for asset families', async () => {
    page.on('request', interceptedRequest => {
      if ('http://pim.com/rest/asset_manager' === interceptedRequest.url() && 'GET' === interceptedRequest.method()) {
        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify({
            items: [],
          }),
        });
      }
    });

    const response = await page.evaluate(async () => {
      // Sometimes this test fails on circle ci. This wait should mitigate that
      await new Promise((resolve) => setTimeout(resolve, 500));

      const fetcher = require('akeneoassetmanager/infrastructure/fetcher/asset-family').default;

      return await fetcher.search();
    });

    expect(response).toEqual({
      items: [],
    });
  });

  it('It fetches one asset family', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/asset_manager/sofa' === interceptedRequest.url() &&
        'GET' === interceptedRequest.method()
      ) {
        const assetFamily = new AssetFamilyBuilder()
          .withIdentifier('sofa')
          .withLabels({
            en_US: 'Sofa',
            fr_FR: 'Canapé',
          })
          .withImage({
            filePath: '/path/sofa.jpg',
            originalFilename: 'sofa.jpg',
          })
          .withAttributes([])
          .withAttributeAsMainMedia('')
          .withAttributeAsLabel('')
          .build();

        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify(assetFamily),
        });
      }
    });

    const response = await page.evaluate(async () => {
      // Sometimes this test fails on circle ci. This wait should mitigate that
      await new Promise((resolve) => setTimeout(resolve, 500));

      const fetcher = require('akeneoassetmanager/infrastructure/fetcher/asset-family').default;

      return await fetcher.fetch('sofa');
    });

    expect(response).toEqual({
      attributes: [],
      assetCount: 123,
      assetFamily: {
        assetCount: 123,
        attributeAsMainMedia: '',
        attributeAsLabel: '',
        attributes: [],
        identifier: 'sofa',
        code: 'sofa',
        labels: {
          en_US: 'Sofa',
          fr_FR: 'Canapé',
        },
        image: {
          filePath: '/path/sofa.jpg',
          originalFilename: 'sofa.jpg',
        },
      },
      permission: {edit: true, assetFamilyIdentifier: 'sofa'},
    });
  });
});
