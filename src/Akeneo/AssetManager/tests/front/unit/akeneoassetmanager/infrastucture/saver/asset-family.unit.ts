'use strict';

import saver from 'akeneoassetmanager/infrastructure/saver/asset-family';
import {createAssetFamilyFromNormalized} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import * as fetch from 'akeneoassetmanager/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('akeneoassetmanager/infrastructure/saver/asset-family', () => {
  it('It saves an asset family', async () => {
    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

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
    expect(fetch.postJSON).toHaveBeenCalledWith('akeneo_asset_manager_asset_family_edit_rest', {
      assetCount: undefined,
      attributeAsLabel: 'label',
      attributeAsMainMedia: 'main_image',
      attributes: undefined,
      code: 'sofa',
      identifier: 'sofa',
      image: null,
      labels: {en_US: 'Sofa', fr_FR: 'Canapé'},
      namingConvention: undefined,
      productLinkRules: undefined,
      transformations: undefined,
    });
  });

  it('It creates an asset family', async () => {
    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

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
    expect(fetch.postJSON).toHaveBeenCalledWith('akeneo_asset_manager_asset_family_create_rest', {
      assetCount: undefined,
      attributeAsLabel: 'label',
      attributeAsMainMedia: 'main_image',
      attributes: undefined,
      code: 'sofa',
      identifier: 'sofa',
      image: null,
      labels: {en_US: 'Sofa', fr_FR: 'Canapé'},
      namingConvention: undefined,
      productLinkRules: undefined,
      transformations: undefined,
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

    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve(errors));

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
