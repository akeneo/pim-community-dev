import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Line, {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import {
  addLines,
  createAssetsFromLines,
  createLineFromFilename,
  getStatusFromLine,
  selectLinesToSend,
} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {NormalizedValidationError} from 'akeneoassetmanager/domain/model/validation-error';

const createAssetFamilyWithMainMedia = (localizable: boolean, scopable: boolean): AssetFamily => {
  return {
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
        value_per_locale: localizable,
        value_per_channel: scopable,
      },
    ],
  };
};

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
    const assetFamily = createAssetFamilyWithMainMedia(false, false);

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
    const assetFamily = createAssetFamilyWithMainMedia(true, false);

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
    const assetFamily = createAssetFamilyWithMainMedia(false, true);

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
    const assetFamily = createAssetFamilyWithMainMedia(true, true);

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
    const assetFamily = createAssetFamilyWithMainMedia(false, false);

    const A = createLineFromFilename('a.png', assetFamily);
    const B = createLineFromFilename('b.png', assetFamily);
    const C = createLineFromFilename('c.png', assetFamily);
    const D = createLineFromFilename('d.png', assetFamily);
    const E = createLineFromFilename('e.png', assetFamily);
    const F = createLineFromFilename('f.png', assetFamily);

    expect(addLines([A, B, C], [D, E, F])).toEqual([D, E, F, A, B, C]);
    expect(addLines([], [D, E, F])).toEqual([D, E, F]);
    expect(addLines([A, B, C], [])).toEqual([A, B, C]);
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> createAssetsFromLines', () => {
  const createUploadedLineFromFilename = (filename: string, assetFamily: AssetFamily) => {
    return {
      ...createLineFromFilename(filename, assetFamily),
      status: LineStatus.Uploaded,
      file: {
        filePath: filename,
        originalFilename: filename,
      },
    };
  };

  test('I can create an asset localizable and scopable from lines with different values for each scope and locale', () => {
    const assetFamily = createAssetFamilyWithMainMedia(true, true);

    const lines = [
      createUploadedLineFromFilename('foo-en_US-ecommerce.jpg', assetFamily),
      createUploadedLineFromFilename('foo-fr_FR-ecommerce.jpg', assetFamily),
      createUploadedLineFromFilename('foo-fr_FR-mobile.jpg', assetFamily),
    ];

    expect(createAssetsFromLines(lines, assetFamily)).toEqual([
      {
        assetFamilyIdentifier: 'packshot',
        code: 'foo',
        labels: {},
        values: [
          {
            attribute: 'picture_fingerprint',
            channel: 'ecommerce',
            locale: 'en_US',
            data: {
              filePath: 'foo-en_US-ecommerce.jpg',
              originalFilename: 'foo-en_US-ecommerce.jpg',
            },
          },
          {
            attribute: 'picture_fingerprint',
            channel: 'ecommerce',
            locale: 'fr_FR',
            data: {
              filePath: 'foo-fr_FR-ecommerce.jpg',
              originalFilename: 'foo-fr_FR-ecommerce.jpg',
            },
          },
          {
            attribute: 'picture_fingerprint',
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
    const assetFamily = createAssetFamilyWithMainMedia(true, true);

    const lines = [createUploadedLineFromFilename('foo.jpg', assetFamily)];

    expect(createAssetsFromLines(lines, assetFamily)).toEqual([
      {
        assetFamilyIdentifier: 'packshot',
        code: 'foo',
        labels: {},
        values: [
          {
            attribute: 'picture_fingerprint',
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
    const assetFamily = createAssetFamilyWithMainMedia(true, true);

    const lines = [
      createUploadedLineFromFilename('foo-en_US-ecommerce.jpg', assetFamily),
      createUploadedLineFromFilename('foo-fr_FR-ecommerce.jpg', assetFamily),
      createUploadedLineFromFilename('bar-en_US-ecommerce.jpg', assetFamily),
      createUploadedLineFromFilename('bar-fr_FR-ecommerce.jpg', assetFamily),
    ];

    expect(createAssetsFromLines(lines, assetFamily)).toEqual([
      {
        assetFamilyIdentifier: 'packshot',
        code: 'foo',
        labels: {},
        values: [
          {
            attribute: 'picture_fingerprint',
            channel: 'ecommerce',
            locale: 'en_US',
            data: {
              filePath: 'foo-en_US-ecommerce.jpg',
              originalFilename: 'foo-en_US-ecommerce.jpg',
            },
          },
          {
            attribute: 'picture_fingerprint',
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
            attribute: 'picture_fingerprint',
            channel: 'ecommerce',
            locale: 'en_US',
            data: {
              filePath: 'bar-en_US-ecommerce.jpg',
              originalFilename: 'bar-en_US-ecommerce.jpg',
            },
          },
          {
            attribute: 'picture_fingerprint',
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
  const defaultLine: Line = {
    id: '68529cb2-4016-427c-b6a2-cda6b118562e',
    thumbnail: null,
    created: false,
    isSending: false,
    file: null,
    filename: 'foo.jpg',
    code: 'foo',
    locale: null,
    channel: null,
    uploadProgress: null,
    errors: {
      back: [],
    },
  };

  const defaultError: NormalizedValidationError = {
    messageTemplate: '',
    parameters: {},
    message: 'error',
    propertyPath: '',
    invalidValue: null,
  };

  test('I can calculate the status of a line localizable and scopable', () => {
    const localizable = true;
    const scopable = true;

    expect(
      getStatusFromLine(
        {
          ...defaultLine,
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.WaitingForUpload);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          isSending: true,
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.UploadInProgress);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          channel: 'ecommerce',
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
          channel: 'ecommerce',
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Valid);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          created: true,
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Created);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          errors: {
            back: [defaultError],
          },
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Invalid);
  });

  test('I can calculate the status of a line localizable and not scopable', () => {
    const localizable = true;
    const scopable = false;

    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Valid);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          channel: 'ecommerce',
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
          channel: 'ecommerce',
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Valid);
  });

  test('I can calculate the status of a line not localizable and scopable', () => {
    const localizable = false;
    const scopable = true;

    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Uploaded);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          channel: 'ecommerce',
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Valid);
    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
          locale: 'en_US',
          channel: 'ecommerce',
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Valid);
  });

  test('I can calculate the status of a line not localizable and not scopable', () => {
    const localizable = false;
    const scopable = false;

    expect(
      getStatusFromLine(
        {
          ...defaultLine,
          file: {
            filePath: 'foo.jpg',
            originalFilename: 'foo.jpg',
          },
        },
        localizable,
        scopable
      )
    ).toEqual(LineStatus.Valid);
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/utils.ts -> selectLinesToSend', () => {
  const defaultLine: Line = {
    id: '68529cb2-4016-427c-b6a2-cda6b118562e',
    thumbnail: null,
    created: false,
    isSending: false,
    file: null,
    filename: 'foo.jpg',
    code: 'foo',
    locale: null,
    channel: null,
    uploadProgress: null,
    errors: {
      back: [],
    },
  };

  test('I can find which lines are ready to be sent', () => {
    const lines = [
      {
        ...defaultLine,
        created: true,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isSending: true,
      },
      {
        ...defaultLine,
        created: false,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isSending: true,
      },
      {
        ...defaultLine,
        created: true,
        file: null,
        isSending: true,
      },
      {
        ...defaultLine,
        created: true,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isSending: false,
      },
      {
        ...defaultLine,
        created: false,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isSending: false,
      },
    ];

    expect(selectLinesToSend(lines)).toEqual([
      {
        ...defaultLine,
        created: false,
        file: {
          filePath: 'foo.jpg',
          originalFilename: 'foo.jpg',
        },
        isSending: false,
      },
    ]);
  });
});
