'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useAssetFamily} from 'akeneoassetmanager/application/hooks/asset-family';
import {renderHook, act} from '@testing-library/react-hooks';

describe('Test asset family hooks', () => {
  test('It can fetch an asset family', async () => {
    const dataProvider = {
      assetFamilyFetcher: {
        fetch: assetFamilyIdentifier =>
          new Promise(resolve => {
            act(() => {
              setTimeout(() => resolve({assetFamily: {code: 'packshot'}}), 100);
            });
          }),
      },
    };
    const {result, waitForNextUpdate} = renderHook(() => useAssetFamily(dataProvider, 'packshot'));

    expect(result.current).toEqual(null);

    await waitForNextUpdate();

    expect(result.current).toEqual({code: 'packshot'});
  });
  test('It does not fetch anything if the asset family identifier is null', async () => {
    const dataProvider = {
      assetFamilyFetcher: {
        fetch: assetFamilyIdentifier =>
          new Promise(resolve => {
            act(() => {
              setTimeout(() => resolve({assetFamily: {code: 'packshot'}}), 100);
            });
          }),
      },
    };
    const {result} = renderHook(() => useAssetFamily(dataProvider, null));

    expect(result.current).toEqual(null);
  });
});
