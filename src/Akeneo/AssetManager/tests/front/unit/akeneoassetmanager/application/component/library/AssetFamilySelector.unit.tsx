'use strict';

import '@testing-library/jest-dom/extend-expect';
import {act} from '@testing-library/react';
import {useAssetFamilyList} from 'akeneoassetmanager/application/component/library/AssetFamilySelector';
import {renderHook} from '@testing-library/react-hooks';

describe('Test file-drop-zone component', () => {
  let container: HTMLElement;

  beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
  });

  test('it provides an empty asset family list', async () => {
    let currentAssetFamily = 'notice';
    const {result, waitForNextUpdate} = renderHook(() =>
      useAssetFamilyList(
        currentAssetFamily,
        {
          assetFamilyFetcher: {
            fetchAll: () =>
              new Promise(async resolve => {
                act(() => resolve([]));
              }),
          },
        },
        newAssetFamily => {
          currentAssetFamily = newAssetFamily;
        }
      )
    );
    expect(currentAssetFamily).toEqual('notice');
    expect(result.current[0]).toEqual([]);
    await waitForNextUpdate();

    expect(currentAssetFamily).toEqual(null);
    expect(result.current[0]).toEqual([]);
  });

  test('it updates the list of asset family and current assetFamily', async () => {
    let currentAssetFamily = 'notice';
    const {result, waitForNextUpdate} = renderHook(() =>
      useAssetFamilyList(
        currentAssetFamily,
        {
          assetFamilyFetcher: {
            fetchAll: () =>
              new Promise(async resolve => {
                act(() =>
                  resolve([
                    {
                      identifier: 'packshot',
                    },
                  ])
                );
              }),
          },
        },
        newAssetFamily => {
          currentAssetFamily = newAssetFamily;
        }
      )
    );
    expect(currentAssetFamily).toEqual('notice');
    expect(result.current[0]).toEqual([]);
    await waitForNextUpdate();

    expect(currentAssetFamily).toEqual('packshot');
    expect(result.current[0]).toEqual([
      {
        identifier: 'packshot',
      },
    ]);
  });
});
