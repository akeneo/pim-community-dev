'use strict';

import saver from 'akeneoassetmanager/infrastructure/saver/asset-family';
import {createAssetFamilyFromNormalized} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';

jest.mock('pim/security-context', () => {}, {virtual: true});

describe('akeneoassetmanager/infrastructure/saver/asset-family', () => {
  it('It saves an asset family', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve(),
        status: 200,
      })
    );

    const savedSofa = createAssetFamilyFromNormalized({
      code: 'sofa',
      identifier: 'sofa',
      labels: {en_US: 'Sofa', fr_FR: 'Canapé'},
      image: createEmptyFile(),
      attribute_as_main_media: 'main_image',
      attribute_as_label: 'label',
    });

    const response = await saver.save(savedSofa);

    expect(response).toEqual(undefined);
    expect(global.fetch).toHaveBeenCalledWith('/rest/asset_manager/sofa', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        identifier: 'sofa',
        code: 'sofa',
        labels: {en_US: 'Sofa', fr_FR: 'Canapé'},
        image: null,
        attributeAsMainMedia: 'main_image',
        attributeAsLabel: 'label',
      }),
    });
  });

  it('It creates an asset family', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve(),
        status: 200,
      })
    );

    const sofaCreated = createAssetFamilyFromNormalized({
      code: 'sofa',
      identifier: 'sofa',
      labels: {en_US: 'Sofa', fr_FR: 'Canapé'},
      image: createEmptyFile(),
      attribute_as_main_media: 'main_image',
      attribute_as_label: 'label',
    });

    const response = await saver.create(sofaCreated);

    expect(response).toEqual(undefined);
    expect(global.fetch).toHaveBeenCalledWith('/rest/asset_manager', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        identifier: 'sofa',
        code: 'sofa',
        labels: {en_US: 'Sofa', fr_FR: 'Canapé'},
        image: null,
        attributeAsMainMedia: 'main_image',
        attributeAsLabel: 'label',
      }),
    });
  });

  it('It returns errors when we create an invalid asset family', async () => {
    const errors = [
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

    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve(errors),
        status: 200,
      })
    );

    const sofaCreated = createAssetFamilyFromNormalized({
      identifier: 'invalid/identifier',
      code: 'invalid/identifier',
      labels: {en_US: 'Sofa', fr_FR: 'Canapé'},
      image: createEmptyFile(),
      attribute_as_main_media: 'main_image',
      attribute_as_label: 'label',
    });

    const response = await saver.create(sofaCreated);
    expect(response).toEqual(errors);
  });
});
