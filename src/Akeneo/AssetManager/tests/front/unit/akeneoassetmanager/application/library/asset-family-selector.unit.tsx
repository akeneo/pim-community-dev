'use strict';

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent} from '@testing-library/react';
import {getByLabelText} from '@testing-library/dom';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {useAssetFamily} from 'akeneoassetmanager/application/library/component/asset-family-selector';
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
      useAssetFamily(
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
      useAssetFamily(
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
