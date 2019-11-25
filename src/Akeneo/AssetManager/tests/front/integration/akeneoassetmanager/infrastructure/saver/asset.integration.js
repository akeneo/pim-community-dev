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
        'designer_starck_1' === JSON.parse(interceptedRequest.postData()).identifier
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createAsset = require('akeneoassetmanager/domain/model/asset/asset').createAsset;
      const createValueCollection = require('akeneoassetmanager/domain/model/asset/value-collection')
        .createValueCollection;
      const createEmptyFile = require('akeneoassetmanager/domain/model/file').createEmptyFile;
      const saver = require('akeneoassetmanager/infrastructure/saver/asset').default;

      const assetCreated = createAsset(
        'designer_starck_1',
        'designer',
        'starck',
        {en_US: 'Stylist', fr_FR: 'Styliste'},
        createEmptyFile(),
        createValueCollection([])
      );

      return await saver.create(assetCreated);
    });

    expect(response).toEqual(undefined);
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
        'invalid/identifier' === JSON.parse(interceptedRequest.postData()).identifier
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
      const createEmptyFile = require('akeneoassetmanager/domain/model/file').createEmptyFile;
      const createValueCollection = require('akeneoassetmanager/domain/model/asset/value-collection')
        .createValueCollection;
      const saver = require('akeneoassetmanager/infrastructure/saver/asset').default;

      const assetCreated = createAsset(
        'invalid/identifier',
        'designer',
        'invalid/identifier',
        {en_US: 'Stylist', fr_FR: 'Styliste'},
        createEmptyFile(),
        createValueCollection([])
      );

      return await saver.create(assetCreated);
    });

    expect(JSON.stringify(response)).toEqual(JSON.stringify(responseMessage));
  });
});
