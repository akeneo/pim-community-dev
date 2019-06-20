const timeout = 5000;

const AssetFamilyBuilder = require('../../../../common/builder/asset-family.js');

describe('Akeneoassetfamily > infrastructure > fetcher > asset-family', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It search for asset families', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/asset_manager' === interceptedRequest.url() &&
        'GET' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify({
            items: [],
          }),
        });
      }
    });

    const response = await page.evaluate(async () => {
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
          .withAttributeAsImage('')
          .withAttributeAsLabel('')
          .build();

        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify(assetFamily),
        });
      }
    });

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoassetmanager/infrastructure/fetcher/asset-family').default;
      const identifierModule = 'akeneoassetmanager/domain/model/asset-family/identifier';
      const assetFamilyIdentifier = require(identifierModule).createIdentifier('sofa');

      return await fetcher.fetch(assetFamilyIdentifier);
    });

    expect(response).toEqual({
      attributes: [],
      assetCount: 123,
      assetFamily: {
        attributeAsImage: {
          identifier: '',
        },
        attributeAsLabel: {
          identifier: '',
        },
        identifier: {
          identifier: 'sofa',
        },
        labelCollection: {
          labels: {
            en_US: 'Sofa',
            fr_FR: 'Canapé',
          },
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
