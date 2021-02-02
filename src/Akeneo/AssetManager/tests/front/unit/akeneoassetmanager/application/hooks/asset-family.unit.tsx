'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useAssetFamily} from 'akeneoassetmanager/application/hooks/asset-family';
import {renderHook, act} from '@testing-library/react-hooks';

jest.mock('akeneoassetmanager/tools/security-context', () => ({
  isGranted: (permission: string) => {
    switch (permission) {
      case 'akeneo_assetmanager_asset_create':
      case 'akeneo_assetmanager_asset_edit':
      case 'akeneo_assetmanager_asset_delete':
      case 'akeneo_assetmanager_asset_family_edit':
        return true;
      case 'akeneo_assetmanager_asset_family_create':
        return false;
    }
  },
}));

const assetFamily = {
  identifier: 'packshot',
  code: 'packshot',
  labels: {en_US: 'Packshot'},
  image: null,
  attributeAsLabel: 'name',
  attributeAsMainMedia: 'picture_fingerprint',
  attributes: [
    {
      identifier: 'name',
      asset_family_identifier: 'name',
      code: 'name',
      type: 'text',
      labels: {en_US: 'Name'},
      order: 0,
      is_required: true,
      value_per_locale: false,
      value_per_channel: false,
    },
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
    },
  ],
  transformations: '[]',
};

const getDataProvider = (edit: boolean) => ({
  assetFamilyFetcher: {
    fetch: assetFamilyIdentifier =>
      new Promise(resolve => {
        act(() => {
          setTimeout(() => resolve({assetFamily, permission: {assetFamilyIdentifier: 'packshot', edit}}), 100);
        });
      }),
  },
});

describe('Test asset family hooks', () => {
  test('It does not fetch anything if the asset family identifier is null', async () => {
    const {result} = renderHook(() => useAssetFamily(getDataProvider(true), null));

    expect(result.current.assetFamily).toEqual(null);
    expect(result.current.rights.assetFamily.edit).toEqual(false);
  });

  test('It can fetch an asset family', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useAssetFamily(getDataProvider(true), 'packshot'));

    expect(result.current.assetFamily).toEqual(null);

    await waitForNextUpdate();

    expect(result.current.assetFamily).toEqual(assetFamily);
    expect(result.current.rights.assetFamily.create).toEqual(false);
    expect(result.current.rights.assetFamily.edit).toEqual(true);
    expect(result.current.rights.asset.create).toEqual(true);
    expect(result.current.rights.asset.upload).toEqual(true);
    expect(result.current.rights.asset.delete).toEqual(true);
  });

  test('It can fetch the rights of the Asset Family', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useAssetFamily(getDataProvider(false), 'packshot'));

    expect(result.current.assetFamily).toEqual(null);

    await waitForNextUpdate();

    expect(result.current.rights.assetFamily.create).toEqual(false);
    expect(result.current.rights.assetFamily.edit).toEqual(false);
    expect(result.current.rights.asset.create).toEqual(false);
    expect(result.current.rights.asset.upload).toEqual(false);
    expect(result.current.rights.asset.delete).toEqual(false);
  });
});
