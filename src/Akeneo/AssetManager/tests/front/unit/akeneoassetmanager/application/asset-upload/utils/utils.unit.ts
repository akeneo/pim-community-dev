'use strict';

import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import {
  addLines,
  createCreationAssetsFromLines,
  createLineFromFilename,
  getAllErrorsOfLineByTarget,
  getStatusFromLine,
  selectLinesToSend,
} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {createFakeAssetFamily, createFakeError, createFakeLine} from '../tools';

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> createLineFromFilename', () => {
  const line = {
    code: 'foo',
    locale: null,
    channel: null,
  };

  const assertLineCreatedMatchExpected = (test: {filename: string; assetFamily: AssetFamily; expected: any}) => {
    let result = createLineFromFilename(test.filename, test.assetFamily);
    expect(result).toMatchObject(test.expected);
    expect(result.filename).toBe(test.filename);
  };

  test('I can create a line from a filename not localizable and not scopable', () => {
    const assetFamily = createFakeAssetFamily(false, false);

    [
      {
        filename: 'foo.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
        },
      },
      {
        filename: 'Foo bar%20.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foobar_20',
        },
      },
      {
        filename: 'foo.jpg.pdf.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo_jpg_pdf',
        },
      },
    ].forEach(test => assertLineCreatedMatchExpected(test));
  });

  test('I can create a line from a filename localizable and not scopable', () => {
    const assetFamily = createFakeAssetFamily(true, false);

    [
      {
        filename: 'foo-en_US.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          locale: 'en_US',
        },
      },
      {
        filename: 'foo-en_US',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          locale: 'en_US',
        },
      },
      {
        filename: 'foo.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          locale: null,
        },
      },
    ].forEach(test => assertLineCreatedMatchExpected(test));
  });

  test('I can create a line from a filename not localizable and scopable', () => {
    const assetFamily = createFakeAssetFamily(false, true);

    [
      {
        filename: 'foo-ecommerce.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          channel: 'ecommerce',
        },
      },
      {
        filename: 'foo-ecommerce',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          channel: 'ecommerce',
        },
      },
      {
        filename: 'foo.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          channel: null,
        },
      },
    ].forEach(test => assertLineCreatedMatchExpected(test));
  });

  test('I can create a line from a filename localizable and scopable', () => {
    const assetFamily = createFakeAssetFamily(true, true);

    [
      {
        filename: 'foo-en_US-ecommerce.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          locale: 'en_US',
          channel: 'ecommerce',
        },
      },
      {
        filename: 'foo-en_US-ecommerce',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          locale: 'en_US',
          channel: 'ecommerce',
        },
      },
      {
        filename: 'foo--ecommerce.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          locale: null,
          channel: 'ecommerce',
        },
      },
      {
        filename: 'foo-en_US-.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          locale: 'en_US',
          channel: null,
        },
      },
      {
        filename: 'foo--.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          locale: null,
          channel: null,
        },
      },
      {
        filename: 'foo.png',
        assetFamily: assetFamily,
        expected: {
          ...line,
          code: 'foo',
          locale: null,
          channel: null,
        },
      },
    ].forEach(test => assertLineCreatedMatchExpected(test));
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> addLines', () => {
  test('I can add new lines', () => {
    const assetFamily = createFakeAssetFamily(false, false);

    const A = createFakeLine('a.png', assetFamily);
    const B = createFakeLine('b.png', assetFamily);
    const C = createFakeLine('c.png', assetFamily);
    const D = createFakeLine('d.png', assetFamily);
    const E = createFakeLine('e.png', assetFamily);
    const F = createFakeLine('f.png', assetFamily);

    expect(addLines([A, B, C], [D, E, F])).toEqual([D, E, F, A, B, C]);
    expect(addLines([], [D, E, F])).toEqual([D, E, F]);
    expect(addLines([A, B, C], [])).toEqual([A, B, C]);
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> createCreationAssetsFromLines', () => {
  const createUploadedLineFromFilename = (filename: string, assetFamily: AssetFamily) => {
    return {
      ...createLineFromFilename(filename, assetFamily),
      file: {
        filePath: filename,
        originalFilename: filename,
      },
    };
  };

  test('I can create an asset localizable and scopable from lines with different values for each scope and locale', () => {
    const assetFamily = createFakeAssetFamily(true, true);

    const lines = [
      createUploadedLineFromFilename('foo-en_US-ecommerce.jpg', assetFamily),
      createUploadedLineFromFilename('foo-fr_FR-ecommerce.jpg', assetFamily),
      createUploadedLineFromFilename('foo-fr_FR-mobile.jpg', assetFamily),
    ];

    const pictureAttribute = {
      identifier: 'picture_fingerprint',
      asset_family_identifier: 'packshot',
      code: 'picture',
      type: 'media_file',
      labels: {en_US: 'Picture'},
      order: 0,
      is_required: true,
      value_per_locale: true,
      value_per_channel: true,
    };

    expect(createCreationAssetsFromLines(lines, assetFamily)).toEqual([
      {
        assetFamilyIdentifier: 'packshot',
        code: 'foo',
        labels: {},
        values: [
          {
            attribute: pictureAttribute,
            channel: 'ecommerce',
            locale: 'en_US',
            data: {
              filePath: 'foo-en_US-ecommerce.jpg',
              originalFilename: 'foo-en_US-ecommerce.jpg',
            },
          },
          {
            attribute: pictureAttribute,
            channel: 'ecommerce',
            locale: 'fr_FR',
            data: {
              filePath: 'foo-fr_FR-ecommerce.jpg',
              originalFilename: 'foo-fr_FR-ecommerce.jpg',
            },
          },
          {
            attribute: pictureAttribute,
            channel: 'mobile',
            locale: 'fr_FR',
            data: {
              filePath: 'foo-fr_FR-mobile.jpg',
              originalFilename: 'foo-fr_FR-mobile.jpg',
            },
          },
        ],
      },
    ]);
  });

  test('I can create an asset localizable and scopable from lines even without the expected filenames', () => {
    const assetFamily = createFakeAssetFamily(true, true);

    const lines = [createUploadedLineFromFilename('foo.jpg', assetFamily)];

    const pictureAttribute = {
      identifier: 'picture_fingerprint',
      asset_family_identifier: 'packshot',
      code: 'picture',
      type: 'media_file',
      labels: {en_US: 'Picture'},
      order: 0,
      is_required: true,
      value_per_locale: true,
      value_per_channel: true,
    };

    expect(createCreationAssetsFromLines(lines, assetFamily)).toEqual([
      {
        assetFamilyIdentifier: 'packshot',
        code: 'foo',
        labels: {},
        values: [
          {
            attribute: pictureAttribute,
            channel: null,
            locale: null,
            data: {
              filePath: 'foo.jpg',
              originalFilename: 'foo.jpg',
            },
          },
        ],
      },
    ]);
  });

  test('I can create several assets localizable and scopable from lines', () => {
    const assetFamily = createFakeAssetFamily(true, true);

    const lines = [
      createUploadedLineFromFilename('foo-en_US-ecommerce.jpg', assetFamily),
      createUploadedLineFromFilename('foo-fr_FR-ecommerce.jpg', assetFamily),
      createUploadedLineFromFilename('bar-en_US-ecommerce.jpg', assetFamily),
      createUploadedLineFromFilename('bar-fr_FR-ecommerce.jpg', assetFamily),
    ];
    const pictureAttribute = {
      identifier: 'picture_fingerprint',
      asset_family_identifier: 'packshot',
      code: 'picture',
      type: 'media_file',
      labels: {en_US: 'Picture'},
      order: 0,
      is_required: true,
      value_per_locale: true,
      value_per_channel: true,
    };

    expect(createCreationAssetsFromLines(lines, assetFamily)).toEqual([
      {
        assetFamilyIdentifier: 'packshot',
        code: 'foo',
        labels: {},
        values: [
          {
            attribute: pictureAttribute,
            channel: 'ecommerce',
            locale: 'en_US',
            data: {
              filePath: 'foo-en_US-ecommerce.jpg',
              originalFilename: 'foo-en_US-ecommerce.jpg',
            },
          },
          {
            attribute: pictureAttribute,
            channel: 'ecommerce',
            locale: 'fr_FR',
            data: {
              filePath: 'foo-fr_FR-ecommerce.jpg',
              originalFilename: 'foo-fr_FR-ecommerce.jpg',
            },
          },
        ],
      },
      {
        assetFamilyIdentifier: 'packshot',
        code: 'bar',
        labels: {},
        values: [
          {
            attribute: pictureAttribute,
            channel: 'ecommerce',
            locale: 'en_US',
            data: {
              filePath: 'bar-en_US-ecommerce.jpg',
              originalFilename: 'bar-en_US-ecommerce.jpg',
            },
          },
          {
            attribute: pictureAttribute,
            channel: 'ecommerce',
            locale: 'fr_FR',
            data: {
              filePath: 'bar-fr_FR-ecommerce.jpg',
              originalFilename: 'bar-fr_FR-ecommerce.jpg',
            },
          },
        ],
      },
    ]);
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> getStatusFromLine', () => {
  test('I can calculate the status of a line localizable and scopable', () => {
    const valuePerLocale = true;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);

    expect(getStatusFromLine(createFakeLine('foo.jpg', assetFamily), valuePerLocale, valuePerChannel)).toEqual(
      LineStatus.WaitingForUpload
    );
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          isFileUploading: true,
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.UploadInProgress);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          channel: 'ecommerce',
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
          channel: 'ecommerce',
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Valid);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          assetCreated: true,
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Created);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          errors: {
            back: [createFakeError('some error')],
          },
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Invalid);
  });

  test('I can calculate the status of a line localizable and not scopable', () => {
    const valuePerLocale = true;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);

    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Valid);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          channel: 'ecommerce',
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
          channel: 'ecommerce',
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Valid);
  });

  test('I can calculate the status of a line not localizable and scopable', () => {
    const valuePerLocale = false;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);

    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          channel: 'ecommerce',
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Valid);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
          channel: 'ecommerce',
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Valid);
  });

  test('I can calculate the status of a line not localizable and not scopable', () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);

    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily),
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Valid);
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> selectLinesToSend', () => {
  test('I can find which lines are ready to be sent', () => {
    const valuePerLocale = false;
    const valuePerChannel = false;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);

    const lines = [
      {
        ...createFakeLine('foo.jpg', assetFamily),
        id: '1',
        assetCreated: true,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isFileUploading: true,
      },
      {
        ...createFakeLine('foo.jpg', assetFamily),
        id: '2',
        assetCreated: false,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isFileUploading: true,
      },
      {
        ...createFakeLine('foo.jpg', assetFamily),
        id: '3',
        assetCreated: true,
        file: null,
        isFileUploading: true,
      },
      {
        ...createFakeLine('foo.jpg', assetFamily),
        id: '4',
        assetCreated: true,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isFileUploading: false,
      },
      {
        ...createFakeLine('foo.jpg', assetFamily),
        id: '5',
        assetCreated: false,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isFileUploading: false,
      },
    ];

    expect(selectLinesToSend(lines)).toEqual([
      {
        ...createFakeLine('foo.jpg', assetFamily),
        id: '5',
        assetCreated: false,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isFileUploading: false,
      },
    ]);
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> getAllErrorsOfLineByTarget', () => {
  test('I can group the errors by target', () => {
    const valuePerLocale = true;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);

    const fileError = {
      ...createFakeError('Error on file size'),
      propertyPath: 'values.file',
    };
    const codeError = {
      ...createFakeError('Error on code'),
      propertyPath: 'code',
    };

    const line = {
      ...createFakeLine('foo.jpg', assetFamily),
      errors: {
        back: [fileError, fileError, codeError, codeError],
      },
    };

    expect(getAllErrorsOfLineByTarget(line)).toEqual({
      all: [fileError, fileError],
      code: [codeError, codeError],
      channel: [],
      locale: [],
    });
  });
});
