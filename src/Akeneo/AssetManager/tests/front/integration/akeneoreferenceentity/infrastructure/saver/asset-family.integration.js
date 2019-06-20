const timeout = 5000;

describe('Akeneoassetfamily > infrastructure > saver > asset-family', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It saves an asset family', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/asset_manager/sofa' === interceptedRequest.url() &&
        'POST' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createAssetFamily = require('akeneoassetmanager/domain/model/asset-family/asset-family')
        .createAssetFamily;
      const createIdentifier = require('akeneoassetmanager/domain/model/asset-family/identifier')
        .createIdentifier;
      const createLabelCollection = require('akeneoassetmanager/domain/model/label-collection')
        .createLabelCollection;
      const createAttributeIdentifier = require('akeneoassetmanager/domain/model/attribute/identifier')
        .createIdentifier;
      const Image = require('akeneoassetmanager/domain/model/file').default;

      const savedSofa = createAssetFamily(
        createIdentifier('sofa'),
        createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'}),
        Image.createEmpty(),
        createAttributeIdentifier(''),
        createAttributeIdentifier('')
      );
      const saver = require('akeneoassetmanager/infrastructure/saver/asset-family').default;

      return await saver.save(savedSofa);
    });

    expect(response).toEqual(undefined);
  });

  it('It creates an asset family', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/rest/asset_manager' === interceptedRequest.url() &&
        'POST' === interceptedRequest.method() &&
        'sofa' === JSON.parse(interceptedRequest.postData()).identifier
      ) {
        interceptedRequest.respond({
          status: 204,
        });
      }
    });

    const response = await page.evaluate(async () => {
      const createAssetFamily = require('akeneoassetmanager/domain/model/asset-family/asset-family')
        .createAssetFamily;
      const createIdentifier = require('akeneoassetmanager/domain/model/asset-family/identifier')
        .createIdentifier;
      const createLabelCollection = require('akeneoassetmanager/domain/model/label-collection')
        .createLabelCollection;
      const createAttributeIdentifier = require('akeneoassetmanager/domain/model/attribute/identifier')
        .createIdentifier;
      const Image = require('akeneoassetmanager/domain/model/file').default;
      const saver = require('akeneoassetmanager/infrastructure/saver/asset-family').default;

      const sofaCreated = createAssetFamily(
        createIdentifier('sofa'),
        createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'}),
        Image.createEmpty(),
        createAttributeIdentifier(''),
        createAttributeIdentifier('')
      );

      return await saver.create(sofaCreated);
    });

    expect(response).toEqual(undefined);
  });

  it('It returns errors when we create an invalid asset family', async () => {
    const responseMessage = [
      {
        messageTemplate: 'This value should not be blank.',
        parameters: {
          '{{ value }}': '',
        },
        plural: null,
        message: 'This value should not be blank.',
        root: {
          identifier: '',
          labels: {
            en_US: 'deefef',
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
        'http://pim.com/rest/asset_manager' === interceptedRequest.url() &&
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
      const createAssetFamily = require('akeneoassetmanager/domain/model/asset-family/asset-family')
        .createAssetFamily;
      const createIdentifier = require('akeneoassetmanager/domain/model/asset-family/identifier')
        .createIdentifier;
      const createLabelCollection = require('akeneoassetmanager/domain/model/label-collection')
        .createLabelCollection;
      const createAttributeIdentifier = require('akeneoassetmanager/domain/model/attribute/identifier')
        .createIdentifier;
      const Image = require('akeneoassetmanager/domain/model/file').default;
      const saver = require('akeneoassetmanager/infrastructure/saver/asset-family').default;

      const sofaCreated = createAssetFamily(
        createIdentifier('invalid/identifier'),
        createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'}),
        Image.createEmpty(),
        createAttributeIdentifier(''),
        createAttributeIdentifier('')
      );

      return await saver.create(sofaCreated);
    });

    expect(JSON.stringify(response)).toEqual(JSON.stringify(responseMessage));
  });
});
