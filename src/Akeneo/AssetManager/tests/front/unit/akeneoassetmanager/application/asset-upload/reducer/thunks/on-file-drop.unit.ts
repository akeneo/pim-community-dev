'use strict';

import {createFakeAssetFamily} from '../../tools';
import {
  fileThumbnailGenerationAction,
  fileUploadProgressAction,
  fileUploadSuccessAction,
  linesAddedAction
} from 'akeneoassetmanager/application/asset-upload/reducer/action';
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {onFileDrop} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/on-file-drop';
import Line from 'src/Akeneo/AssetManager/front/application/asset-upload/model/line';

const flushPromises = () => new Promise(setImmediate);

jest.mock('akeneoassetmanager/application/asset-upload/utils/file', () => ({
  uploadFile: jest.fn().mockImplementation((file: File, line: Line, updateProgress) => {
    updateProgress(line, 0);
    updateProgress(line, 1);
    return Promise.resolve({
      filePath: file.name,
      originalFilename: file.name,
    });
  }),
  getThumbnailFromFile: jest.fn().mockImplementation((file: File, line: Line) =>
    Promise.resolve({
      thumbnail: '/tmb/' + file.name,
      line: line,
    })
  ),
}));

jest.mock('akeneoassetmanager/application/asset-upload/utils/uuid', () => ({
  createUUIDV4: jest.fn().mockImplementation(() => {
    return 'fbf9cff9-e95c-4e7d-983b-2947c7df90df';
  }),
}));

describe('', () => {
  test('Nothing happens if I try to dispatch 0 files', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const files = [];
    const dispatch = jest.fn();

    onFileDrop(files, assetFamily, dispatch);
    await flushPromises();

    expect(dispatch).not.toHaveBeenCalled();
  });

  test('A thumbnail is created when I upload a file', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const files = [file];
    const dispatch = jest.fn();

    onFileDrop(files, assetFamily, dispatch);
    await flushPromises();

    const line = createLineFromFilename(file.name, assetFamily);
    expect(dispatch).toHaveBeenCalledWith(fileThumbnailGenerationAction('/tmb/foo.png', line));
  });

  test('The upload progress is dispatched', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const files = [file];
    const dispatch = jest.fn();

    onFileDrop(files, assetFamily, dispatch);
    await flushPromises();

    const line = createLineFromFilename(file.name, assetFamily);
    expect(dispatch).toHaveBeenCalledWith(fileUploadProgressAction(line, 0));
    expect(dispatch).toHaveBeenCalledWith(fileUploadProgressAction(line, 1));
  });

  test('The upload success is dispatched', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const files = [file];
    const dispatch = jest.fn();

    onFileDrop(files, assetFamily, dispatch);
    await flushPromises();

    const line = createLineFromFilename(file.name, assetFamily);
    expect(dispatch).toHaveBeenCalledWith(fileUploadSuccessAction(line, {
      filePath: 'foo.png',
      originalFilename: 'foo.png',
    }));
    expect(dispatch).toHaveBeenCalledWith(linesAddedAction([line]));
  });
});
