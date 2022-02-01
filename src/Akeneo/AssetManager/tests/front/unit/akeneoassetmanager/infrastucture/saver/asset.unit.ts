'use strict';

import saver from 'akeneoassetmanager/infrastructure/saver/asset';

describe('akeneoassetmanager/infrastructure/saver/asset', () => {
  it('It creates a asset', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve(),
        status: 200,
      })
    );

    const assetCreated = {
      code: 'starck',
      assetFamilyIdentifier: 'designer',
      labels: {en_US: 'Stylist', fr_FR: 'Styliste'},
      values: [],
    };

    const response = await saver.create(assetCreated);

    expect(response).toEqual(null);
  });

  it('It returns errors when we create an invalid asset', async () => {
    const errors = [
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

    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve(errors),
        status: 200,
      })
    );

    const assetCreated = {
      code: 'invalid/identifier',
      assetFamilyIdentifier: 'designer',
      labels: {en_US: 'Stylist', fr_FR: 'Styliste'},
      values: [],
    };

    const response = await saver.create(assetCreated);

    expect(response).toEqual(errors);
  });
});
