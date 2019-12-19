const timeout = 5000;

describe('Akeneoassetfamily > infrastructure > saver > asset', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It creates a asset', async () => {
    page.on('request', interceptedRequest => {
      if (
          'http://pim.com/rest/asset_manager/designer/asset' === interceptedRequest.url() &&
          'POST' === interceptedRequest.method() &&
          'starck' === JSON.parse(interceptedRequest.postData()).code
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createAsset = require('akeneoassetmanager/domain/model/asset/asset').createAsset;

      const saver = require('akeneoassetmanager/infrastructure/saver/asset').default;

      const assetCreated = createAsset(
          'designer_starck_1',
          'designer',
          'image_designer_1234124',
          'starck',
          {en_US: 'Stylist', fr_FR: 'Styliste'},
          [],
          []
      );

      return await saver.create(assetCreated);
    });

    expect(response).toEqual(null);
  });

  it('It returns errors when we create an invalid asset', async () => {
    const responseMessage = [
      {
        messageTemplate: 'This field may only contain letters, numbers and underscores.',
        parameters: {
          '{{ value }}': '/',
        },
        plural: null,
        message: 'pim_asset_manager.asset.validation.identifier.pattern',
        root: {
          identifier: 'invalid/identifier',
          labels: {
            en_US: 'Stylist',
            fr_FR: 'Styliste',
          },
        },
        propertyPath: 'identifier',
        invalidValue: '',
        constraint: {
          defaultOption: null,
          requiredOptions: [],
          targets: 'property',
          payload: null,
        },
        cause: null,
        code: null,
      },
    ];

    page.on('request', interceptedRequest => {
      if (
          'http://pim.com/rest/asset_manager/designer/asset' === interceptedRequest.url() &&
          'POST' === interceptedRequest.method() &&
          'invalid/identifier' === JSON.parse(interceptedRequest.postData()).code
      ) {
        interceptedRequest.respond({
          status: 400,
          contentType: 'application/json',
          body: JSON.stringify(responseMessage),
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createAsset = require('akeneoassetmanager/domain/model/asset/asset').createAsset;

      const saver = require('akeneoassetmanager/infrastructure/saver/asset').default;

      const assetCreated = createAsset(
          'invalid/identifier',
          'designer',
          'image_designer_1234124',
          'invalid/identifier',
          {en_US: 'Stylist', fr_FR: 'Styliste'},
          [],
          []
      );

      return await saver.create(assetCreated);
    });

    expect(JSON.stringify(response)).toEqual(JSON.stringify(responseMessage));
  });
});
