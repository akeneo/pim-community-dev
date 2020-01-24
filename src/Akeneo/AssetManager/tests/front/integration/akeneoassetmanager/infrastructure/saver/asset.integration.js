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
      // Sometimes this test fails on circle ci. This wait should mitigate that
      await new Promise((resolve) => setTimeout(resolve, 500));

      const saver = require('akeneoassetmanager/infrastructure/saver/asset').default;

      const assetCreated = {
        code:'starck',
        assetFamilyIdentifier:'designer',
        labels:{'en_US':'Stylist', 'fr_FR': 'Styliste'},
        values:[]
      };

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
      // Sometimes this test fails on circle ci. This wait should mitigate that
      await new Promise((resolve) => setTimeout(resolve, 500));

      const saver = require('akeneoassetmanager/infrastructure/saver/asset').default;

      const assetCreated = {
        code:'invalid/identifier',
        assetFamilyIdentifier:'designer',
        labels:{'en_US':'Stylist', 'fr_FR': 'Styliste'},
        values:[]
      };

      return await saver.create(assetCreated);
    });

    expect(JSON.stringify(response)).toEqual(JSON.stringify(responseMessage));
  });
});
