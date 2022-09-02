'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useAssetFamily} from 'akeneoassetmanager/application/hooks/asset-family';
import {renderHook} from '@testing-library/react-hooks';
import {FakeConfigProvider} from '../../utils/FakeConfigProvider';
import {BackendAssetFamily} from '../../../../../../front/infrastructure/model/asset-family';

jest.mock('@akeneo-pim-community/shared/lib/hooks/useSecurity', () => ({
  useSecurity: () => ({
    isGranted: (permission: string) => {
      switch (permission) {
        case 'akeneo_assetmanager_asset_create':
        case 'akeneo_assetmanager_asset_edit':
        case 'akeneo_assetmanager_asset_delete':
        case 'akeneo_assetmanager_asset_family_edit':
          return true;
        case 'akeneo_assetmanager_asset_family_create':
        default:
          return false;
      }
    },
  }),
}));

const assetFamily: BackendAssetFamily = {
  identifier: 'packshot',
  code: 'packshot',
  labels: {en_US: 'Packshot'},
  image: null,
  attribute_as_label: 'name',
  attribute_as_main_media: 'picture_fingerprint',
  asset_count: 20,
  attributes: [
    {
      identifier: 'picture_fingerprint',
      asset_family_identifier: 'packshot',
      code: 'picture',
      type: 'media_file',
      labels: {en_US: 'Picture'},
      order: 0,
      is_required: true,
      value_per_locale: true,
      value_per_channel: true,
      is_read_only: false,
      media_type: 'image',
      max_file_size: null,
      allowed_extensions: [],
    },
  ],
  transformations: [],
  product_link_rules: [],
  naming_convention: {},
  permission: {},
};

describe('Test asset family hooks', () => {
  test('It does not fetch anything if the asset family identifier is null', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve({...assetFamily, permission: {edit: true}}),
        status: 200,
      })
    );

    const {result} = renderHook(() => useAssetFamily(null), {wrapper: FakeConfigProvider});

    expect(result.current.assetFamily).toEqual(null);
    expect(result.current.rights.assetFamily.edit).toEqual(false);
  });

  test('It can fetch an asset family', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve({...assetFamily, permission: {edit: true}}),
        status: 200,
      })
    );

    const {result, waitForNextUpdate} = renderHook(() => useAssetFamily('packshot'), {wrapper: FakeConfigProvider});

    expect(result.current.assetFamily).toEqual(null);

    await waitForNextUpdate();

    expect(result.current.assetFamily).toEqual({
      identifier: 'packshot',
      code: 'packshot',
      labels: {en_US: 'Packshot'},
      image: null,
      attributeAsLabel: 'name',
      attributeAsMainMedia: 'picture_fingerprint',
      assetCount: 20,
      attributes: [
        {
          identifier: 'picture_fingerprint',
          asset_family_identifier: 'packshot',
          code: 'picture',
          type: 'media_file',
          labels: {en_US: 'Picture'},
          order: 0,
          is_required: true,
          value_per_locale: true,
          value_per_channel: true,
          is_read_only: false,
          media_type: 'image',
          max_file_size: null,
          allowed_extensions: [],
        },
      ],
      transformations: [],
      productLinkRules: '[]',
      namingConvention: '{}',
    });
    expect(result.current.rights.assetFamily.create).toEqual(false);
    expect(result.current.rights.assetFamily.edit).toEqual(true);
    expect(result.current.rights.asset.create).toEqual(true);
    expect(result.current.rights.asset.upload).toEqual(true);
    expect(result.current.rights.asset.delete).toEqual(true);
  });

  test('It can fetch the rights of the Asset Family', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve({...assetFamily, permission: {edit: false}}),
        status: 200,
      })
    );

    const {result, waitForNextUpdate} = renderHook(() => useAssetFamily('packshot'), {wrapper: FakeConfigProvider});

    expect(result.current.assetFamily).toEqual(null);

    await waitForNextUpdate();

    expect(result.current.rights.assetFamily.create).toEqual(false);
    expect(result.current.rights.assetFamily.edit).toEqual(false);
    expect(result.current.rights.asset.create).toEqual(false);
    expect(result.current.rights.asset.upload).toEqual(false);
    expect(result.current.rights.asset.delete).toEqual(false);
  });
});
