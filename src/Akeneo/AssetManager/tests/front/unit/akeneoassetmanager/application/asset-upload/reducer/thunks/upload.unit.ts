'use strict';

import {createFakeAssetFamily} from '../../tools';
import {
  fileThumbnailGenerationDoneAction,
  fileUploadFailureAction,
  fileUploadProgressAction,
  fileUploadSuccessAction,
  linesAddedAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/line-factory';
import {
  getCurrentQueuedFiles,
  onFileDrop,
  retryFileUpload,
} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/upload';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {uploadFile} from 'akeneoassetmanager/application/asset-upload/utils/file';
import notify from 'akeneoassetmanager/tools/notify';

const flushPromises = () => new Promise(setImmediate);

jest.mock('akeneoassetmanager/tools/notify', () => jest.fn());

const expectQueueInMemoryToBeEmpty = () => expect(Object.values(getCurrentQueuedFiles()).length).toBe(0);
const expectQueueInMemoryToContain = (file: File) =>
  expect(Object.values(getCurrentQueuedFiles())).toContainEqual(file);
const storeInQueueInMemory = (key: string, file: File) => (getCurrentQueuedFiles()[key] = file);

const uploadFileSuccessImpl = (
  _uploader: Function,
  file: File,
  line: Line,
  updateProgress: (line: Line, progress: number) => void
) => {
  updateProgress(line, 0);
  updateProgress(line, 1);

  return Promise.resolve({
    filePath: file.name,
    originalFilename: file.name,
  });
};
const uploadFileFailureImpl = () => Promise.reject();
const uploader = jest.fn();

jest.mock('akeneoassetmanager/application/asset-upload/utils/file', () => ({
  uploadFile: jest.fn().mockImplementation(uploadFileSuccessImpl),
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

describe('onFileDrop', () => {
  test('Nothing happens if I try to dispatch 0 files', async () => {
    uploadFile.mockImplementation(uploadFileSuccessImpl);
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const files: File[] = [];
    const dispatch = jest.fn();

    onFileDrop(uploader, files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    expect(dispatch).not.toHaveBeenCalled();
  });

  test('A thumbnail is created when I upload a file', async () => {
    uploadFile.mockImplementation(uploadFileSuccessImpl);
    const assetFamily = createFakeAssetFamily(false, false);
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const files: File[] = [file];
    const dispatch = jest.fn();

    onFileDrop(uploader, files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    const line = createLineFromFilename(file.name, assetFamily, channels, locales);
    expect(dispatch).toHaveBeenCalledWith(fileThumbnailGenerationDoneAction('/tmb/foo.png', line));
  });

  test('The upload progress is dispatched', async () => {
    uploadFile.mockImplementation(uploadFileSuccessImpl);
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const files = [file];
    const dispatch = jest.fn();

    onFileDrop(uploader, files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    const line = createLineFromFilename(file.name, assetFamily, channels, locales);
    expect(dispatch).toHaveBeenCalledWith(fileUploadProgressAction(line, 0));
    expect(dispatch).toHaveBeenCalledWith(fileUploadProgressAction(line, 1));
  });

  test('The upload success is dispatched', async () => {
    uploadFile.mockImplementation(uploadFileSuccessImpl);
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const files = [file];
    const dispatch = jest.fn();

    onFileDrop(uploader, files, assetFamily, channels, locales, dispatch);
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
    uploadFile.mockImplementation(uploadFileFailureImpl);
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const file = new File(['foo'], 'error', {type: 'image/png'});
    const files = [file];
    const dispatch = jest.fn();

    onFileDrop(uploader, files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    const line = createLineFromFilename(file.name, assetFamily, channels, locales);
    expect(dispatch).toHaveBeenCalledWith(fileUploadFailureAction(line));
    expect(dispatch).toHaveBeenCalledWith(linesAddedAction([line]));
  });

  test('A failed upload is kept in memory', async () => {
    uploadFile.mockImplementation(uploadFileFailureImpl);
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const files = [file];
    const dispatch = jest.fn();

    onFileDrop(uploader, files, assetFamily, channels, locales, dispatch);
    await flushPromises();

    expectQueueInMemoryToContain(file);
  });
});

describe('retryFileUpload', () => {
  test('I can retry a failed upload and keep it in memory if failing again', async () => {
    uploadFile.mockImplementation(uploadFileFailureImpl);
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const dispatch = jest.fn();
    const line = createLineFromFilename(file.name, assetFamily, channels, locales);

    storeInQueueInMemory(line.id, file);

    retryFileUpload(uploader, line, dispatch);
    await flushPromises();

    expectQueueInMemoryToContain(file);
  });

  test('I can retry a failed upload and succeed', async () => {
    uploadFile.mockImplementation(uploadFileSuccessImpl);
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const dispatch = jest.fn();
    const line = createLineFromFilename(file.name, assetFamily, channels, locales);

    storeInQueueInMemory(line.id, file);

    retryFileUpload(uploader, line, dispatch);
    await flushPromises();

    expectQueueInMemoryToBeEmpty();
    expect(dispatch).toHaveBeenCalledWith(
      fileUploadSuccessAction(line, {
        filePath: 'foo.png',
        originalFilename: 'foo.png',
      })
    );
  });

  test('I can retry on an unknown line and nothing will happen', async () => {
    uploadFile.mockImplementation(uploadFileSuccessImpl);
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const dispatch = jest.fn();
    const line = createLineFromFilename(file.name, assetFamily, channels, locales);

    retryFileUpload(uploader, line, dispatch);
    await flushPromises();

    expectQueueInMemoryToBeEmpty();
    expect(notify).toHaveBeenCalled();
  });
});
