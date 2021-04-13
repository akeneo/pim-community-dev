'use strict';

import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import {
  addLines,
  createCreationAssetsFromLines,
  getAllErrorsOfLineByTarget,
  getStatusFromLine,
  selectLinesToSend,
  hasAnUnsavedLine,
  getCreatedAssetCodes,
} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/line-factory';
import {createFakeAssetFamily, createFakeChannel, createFakeError, createFakeLine, createFakeLocale} from '../tools';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> addLines', () => {
  test('I can add new lines', () => {
    const assetFamily = createFakeAssetFamily(false, false);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    const A = createFakeLine('a.png', assetFamily, channels, locales);
    const B = createFakeLine('b.png', assetFamily, channels, locales);
    const C = createFakeLine('c.png', assetFamily, channels, locales);
    const D = createFakeLine('d.png', assetFamily, channels, locales);
    const E = createFakeLine('e.png', assetFamily, channels, locales);
    const F = createFakeLine('f.png', assetFamily, channels, locales);

    expect(addLines([A, B, C], [D, E, F])).toEqual([D, E, F, A, B, C]);
    expect(addLines([], [D, E, F])).toEqual([D, E, F]);
    expect(addLines([A, B, C], [])).toEqual([A, B, C]);
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> createCreationAssetsFromLines', () => {
  const createUploadedLineFromFilename = (
    filename: string,
    assetFamily: AssetFamily,
    channels: Channel[] = [],
    locales: Locale[] = []
  ) => {
    return {
      ...createLineFromFilename(filename, assetFamily, channels, locales),
      file: {
        filePath: filename,
        originalFilename: filename,
      },
    };
  };

  test('I can create an asset localizable and scopable from lines with different values for each scope and locale', () => {
    const assetFamily = createFakeAssetFamily(true, true);
    const channels: Channel[] = [
      createFakeChannel('ecommerce', ['en_US', 'fr_FR']),
      createFakeChannel('mobile', ['en_US', 'fr_FR']),
    ];
    const locales: Locale[] = [createFakeLocale('en_US'), createFakeLocale('fr_FR')];

    const lines = [
      createUploadedLineFromFilename('foo-en_US-ecommerce.jpg', assetFamily, channels, locales),
      createUploadedLineFromFilename('foo-fr_FR-ecommerce.jpg', assetFamily, channels, locales),
      createUploadedLineFromFilename('foo-fr_FR-mobile.jpg', assetFamily, channels, locales),
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
    const channels: Channel[] = [
      createFakeChannel('ecommerce', ['en_US', 'fr_FR']),
      createFakeChannel('mobile', ['en_US', 'fr_FR']),
    ];
    const locales: Locale[] = [createFakeLocale('en_US'), createFakeLocale('fr_FR')];

    const lines = [createUploadedLineFromFilename('foo.jpg', assetFamily, channels, locales)];

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
    const channels: Channel[] = [
      createFakeChannel('ecommerce', ['en_US', 'fr_FR']),
      createFakeChannel('mobile', ['en_US', 'fr_FR']),
    ];
    const locales: Locale[] = [createFakeLocale('en_US'), createFakeLocale('fr_FR')];

    const lines = [
      createUploadedLineFromFilename('foo-en_US-ecommerce.jpg', assetFamily, channels, locales),
      createUploadedLineFromFilename('foo-fr_FR-ecommerce.jpg', assetFamily, channels, locales),
      createUploadedLineFromFilename('bar-en_US-ecommerce.jpg', assetFamily, channels, locales),
      createUploadedLineFromFilename('bar-fr_FR-ecommerce.jpg', assetFamily, channels, locales),
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
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    expect(
      getStatusFromLine(createFakeLine('foo.jpg', assetFamily, channels, locales), valuePerLocale, valuePerChannel)
    ).toEqual(LineStatus.WaitingForUpload);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
          isFileUploading: true,
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.UploadInProgress);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
          assetCreated: true,
        },
        valuePerLocale,
        valuePerChannel
      )
    ).toEqual(LineStatus.Created);
    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
          errors: {
            back: [createFakeError('some error')],
            front: [],
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
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    expect(
      getStatusFromLine(
        {
          ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    const lines = [
      {
        ...createFakeLine('foo.jpg', assetFamily, channels, locales),
        id: '1',
        assetCreated: true,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isFileUploading: true,
      },
      {
        ...createFakeLine('foo.jpg', assetFamily, channels, locales),
        id: '2',
        assetCreated: false,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isFileUploading: true,
      },
      {
        ...createFakeLine('foo.jpg', assetFamily, channels, locales),
        id: '3',
        assetCreated: true,
        file: null,
        isFileUploading: true,
      },
      {
        ...createFakeLine('foo.jpg', assetFamily, channels, locales),
        id: '4',
        assetCreated: true,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isFileUploading: false,
      },
      {
        ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
        ...createFakeLine('foo.jpg', assetFamily, channels, locales),
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
  test('I can group the errors, by default, on the file', () => {
    const valuePerLocale = true;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    const unknownError = {
      ...createFakeError(),
      propertyPath: undefined,
    };

    const error = {
      ...createFakeError(),
      propertyPath: 'foobar',
    };

    const line = {
      ...createFakeLine('foo.jpg', assetFamily, channels, locales),
      errors: {
        back: [error, unknownError],
        front: [],
      },
    };

    expect(getAllErrorsOfLineByTarget(line)).toEqual({
      common: [error, unknownError],
      code: [],
      channel: [],
      locale: [],
    });
  });

  test('I can group the errors on the channel', () => {
    const valuePerLocale = true;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    const error = {
      ...createFakeError(),
      propertyPath: 'values.foo.channel',
    };

    const line = {
      ...createFakeLine('foo.jpg', assetFamily, channels, locales),
      errors: {
        back: [error],
        front: [],
      },
    };

    expect(getAllErrorsOfLineByTarget(line)).toEqual({
      common: [],
      code: [],
      channel: [error],
      locale: [],
    });
  });

  test('I can group the errors on the locale', () => {
    const valuePerLocale = true;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    const error = {
      ...createFakeError(),
      propertyPath: 'values.foo.locale',
    };

    const line = {
      ...createFakeLine('foo.jpg', assetFamily, channels, locales),
      errors: {
        back: [error],
        front: [],
      },
    };

    expect(getAllErrorsOfLineByTarget(line)).toEqual({
      common: [],
      code: [],
      channel: [],
      locale: [error],
    });
  });

  test('I can group the errors on the code', () => {
    const valuePerLocale = true;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    const error = {
      ...createFakeError(),
      propertyPath: 'code',
    };

    const line = {
      ...createFakeLine('foo.jpg', assetFamily, channels, locales),
      errors: {
        back: [error],
        front: [],
      },
    };

    expect(getAllErrorsOfLineByTarget(line)).toEqual({
      common: [],
      code: [error],
      channel: [],
      locale: [],
    });
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> hasAnUnsavedLine', () => {
  test('I can check if there is an unsaved line', () => {
    const valuePerLocale = true;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    const lines = [
      {
        ...createFakeLine('a.jpg', assetFamily, channels, locales),
        assetCreated: true,
      },
      {
        ...createFakeLine('b.jpg', assetFamily, channels, locales),
        assetCreated: false,
      },
      {
        ...createFakeLine('c.jpg', assetFamily, channels, locales),
        assetCreated: true,
      },
    ];

    expect(hasAnUnsavedLine(lines, valuePerLocale, valuePerChannel)).toEqual(true);
  });

  test('I can check if all the lines are saved', () => {
    const valuePerLocale = true;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
    const channels: Channel[] = [];
    const locales: Locale[] = [];

    const lines = [
      {
        ...createFakeLine('a.jpg', assetFamily, channels, locales),
        assetCreated: true,
      },
      {
        ...createFakeLine('b.jpg', assetFamily, channels, locales),
        assetCreated: true,
      },
      {
        ...createFakeLine('c.jpg', assetFamily, channels, locales),
        assetCreated: true,
      },
    ];

    expect(hasAnUnsavedLine(lines, valuePerLocale, valuePerChannel)).toEqual(false);
  });

  test('I can get created asset codes from lines', () => {
    const valuePerLocale = true;
    const valuePerChannel = true;
    const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);

    const lines = [
      {
        ...createFakeLine('a.jpg', assetFamily),
        assetCreated: true,
      },
      {
        ...createFakeLine('b.jpg', assetFamily),
        assetCreated: false,
      },
      {
        ...createFakeLine('c.jpg', assetFamily),
        assetCreated: true,
      },
    ];

    expect(getCreatedAssetCodes(lines)).toEqual(['a', 'c']);
  });
});
