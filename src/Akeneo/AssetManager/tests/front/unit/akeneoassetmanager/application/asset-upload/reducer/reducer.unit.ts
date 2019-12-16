'use strict';

import {reducer} from 'akeneoassetmanager/application/asset-upload/reducer/reducer';
import {
  assetCreationFailAction,
  assetCreationSuccessAction,
  editLineAction,
  fileThumbnailGenerationAction,
  fileUploadProgressAction,
  fileUploadSuccessAction,
  lineCreationStartAction,
  linesAddedAction,
  removeAllLinesAction,
  removeLineAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import {createFakeAssetFamily, createFakeCreationAsset, createFakeError, createFakeLine} from '../tools';

const valuePerLocale = true;
const valuePerChannel = true;
const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);

const lines = {
  A: createFakeLine('a.jpg', assetFamily),
  B: createFakeLine('b.jpg', assetFamily),
  C: createFakeLine('c.jpg', assetFamily),
  D: createFakeLine('d.jpg', assetFamily),
  E: createFakeLine('e.jpg', assetFamily),
  F: createFakeLine('f.jpg', assetFamily),
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

    const action = fileThumbnailGenerationAction('foo_tmb.png', lines.A);
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
          isSending: false,
        },
        lines.B,
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
          isSending: true,
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
        },
        lines.B,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can mark a line as successfully created', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B],
    });

    const asset = createFakeCreationAsset(lines.A.code, assetFamily);

    const action = assetCreationSuccessAction(asset);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          assetCreated: true,
          isAssetCreating: false,
        },
        lines.B,
      ],
    };

    expect(newState).toEqual(expectedState);
  });

  test('I can mark a line as failed creation', () => {
    const state = Object.freeze({
      ...defaultState,
      lines: [lines.A, lines.B],
    });

    const asset = createFakeCreationAsset(lines.A.code, assetFamily);
    const errors = [createFakeError('some error')];

    const action = assetCreationFailAction(asset, errors);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          assetCreated: false,
          isAssetCreating: false,
          errors: {
            back: errors,
          },
        },
        lines.B,
      ],
    };

    expect(newState).toEqual(expectedState);
  });
});
