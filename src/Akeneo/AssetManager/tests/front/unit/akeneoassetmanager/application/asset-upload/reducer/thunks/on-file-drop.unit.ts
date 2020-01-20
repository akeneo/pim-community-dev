'use strict';

import {createFakeAssetFamily} from '../../tools';
import {
  fileThumbnailGenerationDoneAction,
  fileUploadProgressAction,
  fileUploadSuccessAction,
  fileUploadFailureAction,
  linesAddedAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {onFileDrop} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/on-file-drop';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {uploadFile} from 'akeneoassetmanager/application/asset-upload/utils/file';
import notify from 'akeneoassetmanager/tools/notify';

const flushPromises = () => new Promise(setImmediate);

jest.mock('akeneoassetmanager/tools/notify', () => jest.fn());

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
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const files: File[] = [];
    const dispatch = jest.fn();

    onFileDrop(files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    expect(dispatch).not.toHaveBeenCalled();
  });

  test('A thumbnail is created when I upload a file', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const files: File[] = [file];
    const dispatch = jest.fn();

    onFileDrop(files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    const line = createLineFromFilename(file.name, assetFamily, channels, locales);
    expect(dispatch).toHaveBeenCalledWith(fileThumbnailGenerationDoneAction('/tmb/foo.png', line));
  });

  test('The upload progress is dispatched', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const files = [file];
    const dispatch = jest.fn();

    onFileDrop(files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    const line = createLineFromFilename(file.name, assetFamily, channels, locales);
    expect(dispatch).toHaveBeenCalledWith(fileUploadProgressAction(line, 0));
    expect(dispatch).toHaveBeenCalledWith(fileUploadProgressAction(line, 1));
  });

  test('The upload success is dispatched', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const files = [file];
    const dispatch = jest.fn();

    onFileDrop(files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    const line = createLineFromFilename(file.name, assetFamily, channels, locales);
    expect(dispatch).toHaveBeenCalledWith(
      fileUploadSuccessAction(line, {
        filePath: 'foo.png',
        originalFilename: 'foo.png',
      })
    );
    expect(dispatch).toHaveBeenCalledWith(linesAddedAction([line]));
  });

  test('The upload is not dispatched on failure', async () => {
    uploadFile.mockImplementation(() => Promise.reject());
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const file = new File(['foo'], 'error', {type: 'image/png'});
    const files = [file];
    const dispatch = jest.fn();

    onFileDrop(files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    const line = createLineFromFilename(file.name, assetFamily, channels, locales);
    expect(dispatch).toHaveBeenCalledWith(fileUploadFailureAction(line));
    expect(dispatch).toHaveBeenCalledWith(linesAddedAction([line]));
  });

  test('I can upload files up to a certain amount', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const dispatch = jest.fn();
    const files = Array.from(Array(499).keys()).map(index => new File(['foo'], index + '.png', {type: 'image/png'}));

    onFileDrop(files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    expect(notify).not.toHaveBeenCalled();
    const lines = files.map(file => createLineFromFilename(file.name, assetFamily, channels, locales));
    expect(dispatch).toHaveBeenCalledWith(linesAddedAction(lines));
  });

  test('I cannot upload more files than allowed', async () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const dispatch = jest.fn();
    const files = Array.from(Array(501).keys()).map(index => new File(['foo'], index + '.png', {type: 'image/png'}));

    onFileDrop(files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    expect(notify).toHaveBeenCalled();
    const lines = files.slice(0, 500).map(file => createLineFromFilename(file.name, assetFamily, channels, locales));
    expect(dispatch).toHaveBeenCalledWith(linesAddedAction(lines));
  });
});
