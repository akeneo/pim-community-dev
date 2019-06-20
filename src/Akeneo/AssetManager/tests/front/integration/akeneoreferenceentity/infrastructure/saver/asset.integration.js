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
      const createAssetCode = require('akeneoassetmanager/domain/model/asset/code').createCode;
      const createValueCollection = require('akeneoassetmanager/domain/model/asset/value-collection')
        .createValueCollection;
      const createIdentifier = require('akeneoassetmanager/domain/model/asset/identifier').createIdentifier;
      const createAssetFamilyIdentifier = require('akeneoassetmanager/domain/model/asset-family/identifier')
        .createIdentifier;
      const Image = require('akeneoassetmanager/domain/model/file').default;
      const createLabelCollection = require('akeneoassetmanager/domain/model/label-collection')
        .createLabelCollection;
      const saver = require('akeneoassetmanager/infrastructure/saver/asset').default;

      const assetCreated = createAsset(
        createIdentifier('designer_starck_1'),
        createAssetFamilyIdentifier('designer'),
        createAssetCode('starck'),
        createLabelCollection({en_US: 'Stylist', fr_FR: 'Styliste'}),
        Image.createEmpty(),
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
      const createAssetCode = require('akeneoassetmanager/domain/model/asset/code').createCode;
      const createIdentifier = require('akeneoassetmanager/domain/model/asset/identifier').createIdentifier;
      const createAssetFamilyIdentifier = require('akeneoassetmanager/domain/model/asset-family/identifier')
        .createIdentifier;
      const Image = require('akeneoassetmanager/domain/model/file').default;
      const createLabelCollection = require('akeneoassetmanager/domain/model/label-collection')
        .createLabelCollection;
      const createValueCollection = require('akeneoassetmanager/domain/model/asset/value-collection')
        .createValueCollection;
      const saver = require('akeneoassetmanager/infrastructure/saver/asset').default;

      const assetCreated = createAsset(
        createIdentifier('invalid/identifier'),
        createAssetFamilyIdentifier('designer'),
        createAssetCode('invalid/identifier'),
        createLabelCollection({en_US: 'Stylist', fr_FR: 'Styliste'}),
        Image.createEmpty(),
        createValueCollection([])
      );

      return await saver.create(assetCreated);
    });

    expect(JSON.stringify(response)).toEqual(JSON.stringify(responseMessage));
  });
});
