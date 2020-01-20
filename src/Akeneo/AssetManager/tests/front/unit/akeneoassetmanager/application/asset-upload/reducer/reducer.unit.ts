'use strict';

import {reducer} from 'akeneoassetmanager/application/asset-upload/reducer/reducer';
import {
  assetCreationFailAction,
  assetCreationSuccessAction,
  editLineAction,
  fileThumbnailGenerationDoneAction,
  fileUploadProgressAction,
  fileUploadSuccessAction,
  fileUploadFailureAction,
  lineCreationStartAction,
  linesAddedAction,
  removeAllLinesAction,
  removeLineAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import {createFakeAssetFamily, createFakeCreationAsset, createFakeError, createFakeLine} from '../tools';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';

const valuePerLocale = true;
const valuePerChannel = true;
const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
const channels: Channel[] = [];
const locales: Locale[] = [];

const lines = {
  A: createFakeLine('a.jpg', assetFamily, channels, locales),
  B: createFakeLine('b.jpg', assetFamily, channels, locales),
  C: createFakeLine('c.jpg', assetFamily, channels, locales),
  D: createFakeLine('d.jpg', assetFamily, channels, locales),
  E: createFakeLine('e.jpg', assetFamily, channels, locales),
  F: createFakeLine('f.jpg', assetFamily, channels, locales),
};

const defaultState = {
  lines: [],
};

describe('akeneoassetmanager/application/asset-upload/reducer/reducer.ts', () => {
  test('I can pass other actions', () => {
    const state = Object.freeze({
      ...defaultState,
    });

    // @ts-ignore
    const newState = reducer(state, {type: 'FOO_BAR'});

    const expectedState = {
      ...defaultState,
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can add lines', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B],
    });

    const action = linesAddedAction([lines.C, lines.D]);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [lines.C, lines.D, lines.A, lines.B],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can remove a line', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B, lines.C],
    });

    const action = removeLineAction(lines.B);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [lines.A, lines.C],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can remove all the lines', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B, lines.C],
    });

    const action = removeAllLinesAction();
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can update the thumbnail of a line', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B],
    });

    const action = fileThumbnailGenerationDoneAction('foo_tmb.png', lines.A);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          thumbnail: 'foo_tmb.png',
        },
        lines.B,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can update the code of a line', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B],
    });

    const action = editLineAction({
      ...lines.A,
      code: 'foobar',
    });
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          code: 'foobar',
        },
        lines.B,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can update the locale of a line', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B],
    });

    const action = editLineAction({
      ...lines.A,
      locale: 'en_US',
    });
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          locale: 'en_US',
        },
        lines.B,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can update the channel of a line', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B],
    });

    const action = editLineAction({
      ...lines.A,
      channel: 'ecommerce',
    });
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          channel: 'ecommerce',
        },
        lines.B,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can mark a line as uploaded', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B],
    });

    const file = Object.freeze({
      filePath: 'foobar.jpg',
      originalFilename: 'a.jpg',
    } as FileModel);

    const action = fileUploadSuccessAction(lines.A, file);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          file: file,
          isFileUploading: false,
        },
        lines.B,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can mark a line as failed to upload', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B, lines.C],
    });

    const action = fileUploadFailureAction(lines.B);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.B,
          isFileUploading: false,
          errors: {
            back: [],
            front: [createFakeError('pim_asset_manager.asset.upload.upload_failure')],
          },
        },
        lines.A,
        lines.C,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can update the progress of the upload of a line', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B],
    });

    const action = fileUploadProgressAction(lines.A, 0.4);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          uploadProgress: 0.4,
          isFileUploading: true,
        },
        lines.B,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can mark a line as being created', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B],
    });

    const action = lineCreationStartAction(lines.A);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          isAssetCreating: true,
          errors: {
            back: [],
            front: [],
          },
        },
        lines.B,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can mark a line as successfully created', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B, lines.C],
    });

    const asset = createFakeCreationAsset(lines.B.code, assetFamily);

    const action = assetCreationSuccessAction(asset);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        lines.A,
        {
          ...lines.B,
          assetCreated: true,
          isAssetCreating: false,
        },
        lines.C,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can mark a line as failed creation', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B, lines.C],
    });

    const asset = createFakeCreationAsset(lines.B.code, assetFamily);
    const errors = [createFakeError('some error')];

    const action = assetCreationFailAction(asset, errors);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.B,
          assetCreated: false,
          isAssetCreating: false,
          errors: {
            back: errors,
            front: [],
          },
        },
        lines.A,
        lines.C,
      ],
    };

    expect(newState).toEqual(expectedState);
  });
});
