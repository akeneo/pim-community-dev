'use strict';

import {createFakeAssetFamily, createFakeError, createFakeLine} from '../../tools';
import {onCreateAllAsset} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/on-create-all-assets';
import {
  assetCreationFailAction,
  assetCreationSuccessAction,
  lineCreationStartAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';
import {createCreationAssetsFromLines} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {create} from 'akeneoassetmanager/application/asset-upload/saver/asset';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';

const flushPromises = () => new Promise(setImmediate);

jest.mock('akeneoassetmanager/application/asset-upload/saver/asset', () => ({
  create: jest.fn(),
}));

describe('', () => {
  test('I can create an asset from a line', async () => {
    create.mockImplementation(() => Promise.resolve(null));

    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = {
      ...createFakeLine('a.png', assetFamily, channels, locales),
      assetCreated: false,
      file: {
        filePath: 'a.png',
        originalFilename: 'a.png',
      },
      isFileUploading: false,
    };
    const dispatch = jest.fn();

    onCreateAllAsset(assetFamily, [line], dispatch);
    await flushPromises();

    expect(dispatch).toHaveBeenCalledWith(lineCreationStartAction(line));
    let asset = createCreationAssetsFromLines([line], assetFamily)[0];
    expect(dispatch).toHaveBeenCalledWith(assetCreationSuccessAction(asset));
  });

  test('I dispatch the validation error from the server when the creation failed', async () => {
    const errors = [createFakeError('some error')];
    create.mockImplementation(() => Promise.resolve(errors));

    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = {
      ...createFakeLine('a.png', assetFamily, channels, locales),
      assetCreated: false,
      file: {
        filePath: 'a.png',
        originalFilename: 'a.png',
      },
      isFileUploading: false,
    };
    const dispatch = jest.fn();

    onCreateAllAsset(assetFamily, [line], dispatch);
    await flushPromises();

    expect(dispatch).toHaveBeenCalledWith(lineCreationStartAction(line));
    let asset = createCreationAssetsFromLines([line], assetFamily)[0];
    expect(dispatch).toHaveBeenCalledWith(assetCreationFailAction(asset, errors));
  });

  test('I dispatch the fatal error from the server when the creation failed', async () => {
    create.mockImplementation(() => {
      throw new Error();
    });

    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const line = {
      ...createFakeLine('a.png', assetFamily, channels, locales),
      assetCreated: false,
      file: {
        filePath: 'a.png',
        originalFilename: 'a.png',
      },
      isFileUploading: false,
    };
    const dispatch = jest.fn();

    onCreateAllAsset(assetFamily, [line], dispatch);
    await flushPromises();

    expect(dispatch).toHaveBeenCalledWith(lineCreationStartAction(line));
    let asset = createCreationAssetsFromLines([line], assetFamily)[0];
    expect(dispatch).toHaveBeenCalledWith(
      assetCreationFailAction(asset, [
        {
          ...createFakeError(),
          message: 'Internal server error',
          messageTemplate: 'pim_asset_manager.asset.validation.server_error',
          invalidValue: asset,
        },
      ])
    );
  });
});
