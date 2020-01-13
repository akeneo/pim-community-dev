'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useAssetFamily} from 'akeneoassetmanager/application/hooks/asset-family';
import {renderHook, act} from '@testing-library/react-hooks';

jest.mock('akeneoassetmanager/tools/security-context', () => ({isGranted: (permission: string) => true}));

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

const dataProvider = {
  assetFamilyFetcher: {
    fetch: assetFamilyIdentifier =>
      new Promise(resolve => {
        act(() => {
          setTimeout(() => resolve({assetFamily, permission: {assetFamilyIdentifier: 'packshot', edit: true}}), 100);
        });
      }),
  },
};

describe('Test asset family hooks', () => {
  test('It can fetch an asset family', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useAssetFamily(dataProvider, 'packshot'));

    expect(result.current.assetFamily).toEqual(null);

    await waitForNextUpdate();

    expect(result.current.assetFamily).toEqual(assetFamily);
    expect(result.current.rights.assetFamily.edit).toEqual(true);
  });

  test('It does not fetch anything if the asset family identifier is null', async () => {
    const {result} = renderHook(() => useAssetFamily(dataProvider, null));

    expect(result.current.assetFamily).toEqual(null);
    expect(result.current.rights.assetFamily.edit).toEqual(false);
  });
});
