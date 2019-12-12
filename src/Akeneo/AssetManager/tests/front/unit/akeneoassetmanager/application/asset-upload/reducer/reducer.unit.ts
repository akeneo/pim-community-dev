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
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';

const assetFamily = Object.freeze({
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
});

const defaultError = Object.freeze({
  messageTemplate: '',
  parameters: {},
  message: 'error',
  propertyPath: '',
  invalidValue: null,
});

const lines = {
  A: Object.freeze(createLineFromFilename('a.jpg', assetFamily)),
  B: Object.freeze(createLineFromFilename('b.jpg', assetFamily)),
  C: Object.freeze(createLineFromFilename('c.jpg', assetFamily)),
  D: Object.freeze(createLineFromFilename('d.jpg', assetFamily)),
  E: Object.freeze(createLineFromFilename('e.jpg', assetFamily)),
  F: Object.freeze(createLineFromFilename('f.jpg', assetFamily)),
};

const defaultState = {
  lines: [],
};

describe('akeneoassetmanager/application/asset-upload/reducer/asset-upload.ts -> reducer', () => {
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
          isCreating: true,
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

    const asset: CreationAsset = {
      assetFamilyIdentifier: assetFamily.identifier,
      code: lines.A.code,
      labels: {},
      values: [],
    };

    const action = assetCreationSuccessAction(asset);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          created: true,
          isCreating: false,
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

    const asset: CreationAsset = {
      assetFamilyIdentifier: assetFamily.identifier,
      code: lines.A.code,
      labels: {},
      values: [],
    };
    const errors = [
      {
        ...defaultError,
        message: 'foobar',
      },
    ];

    const action = assetCreationFailAction(asset, errors);
    const newState = reducer(state, action);

    const expectedState = {
      ...defaultState,
      lines: [
        {
          ...lines.A,
          created: false,
          isCreating: false,
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
