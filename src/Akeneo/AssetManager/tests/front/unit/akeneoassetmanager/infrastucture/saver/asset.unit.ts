'use strict';

import saver from 'akeneoassetmanager/infrastructure/saver/asset';
import * as fetch from 'akeneoassetmanager/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('akeneoassetmanager/infrastructure/saver/asset', () => {
  it('It creates a asset', async () => {
    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

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

    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve(errors));

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
