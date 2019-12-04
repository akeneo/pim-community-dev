import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import {
  addLines,
  createAssetsFromLines,
  createLineFromFilename
} from 'akeneoassetmanager/application/asset-upload/utils/utils';

const assetFamily: AssetFamily = {
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
  ]
};

const pictureAttribute: NormalizedAttribute = {
  identifier: 'picture_fingerprint',
  asset_family_identifier: 'packshot',
  code: 'picture',
  type: 'media_file',
  labels: {en_US: 'Picture'},
  order: 0,
  is_required: true,
  value_per_locale: false,
  value_per_channel: false,
};

const assetFamilyWithMainMediaNotLocalizableNotScopable: AssetFamily = {
  ...assetFamily,
  attributes: [
    ...assetFamily.attributes,
    {
      ...pictureAttribute,
      value_per_locale: false,
      value_per_channel: false,
    },
  ]
};

const assetFamilyWithMainMediaLocalizableNotScopable: AssetFamily = {
  ...assetFamily,
  attributes: [
    ...assetFamily.attributes,
    {
      ...pictureAttribute,
      value_per_locale: true,
      value_per_channel: false,
    },
  ]
};

const assetFamilyWithMainMediaNotLocalizableScopable: AssetFamily = {
  ...assetFamily,
  attributes: [
    ...assetFamily.attributes,
    {
      ...pictureAttribute,
      value_per_locale: false,
      value_per_channel: true,
    },
  ]
};

const assetFamilyWithMainMediaLocalizableScopable: AssetFamily = {
  ...assetFamily,
  attributes: [
    ...assetFamily.attributes,
    {
      ...pictureAttribute,
      value_per_locale: true,
      value_per_channel: true,
    },
  ]
};

describe('src/Akeneo/AssetManager/front/application/asset-upload/utils/utils.ts -> createLineFromFilename', () => {
  const line = {
    code: 'foo',
    locale: null,
    channel: null,
    status: LineStatus.WaitingForUpload,
    uploadProgress: null,
  };

  const values = [
    {
      filename: 'foo.png',
      assetFamily: assetFamilyWithMainMediaNotLocalizableNotScopable,
      expected: {
        ...line,
        code: 'foo',
      },
    },
    {
      filename: 'foo-en_US.png',
      assetFamily: assetFamilyWithMainMediaLocalizableNotScopable,
      expected: {
        ...line,
        code: 'foo',
        locale: 'en_US',
      },
    },
    {
      filename: 'foo-en_US',
      assetFamily: assetFamilyWithMainMediaLocalizableNotScopable,
      expected: {
        ...line,
        code: 'foo',
        locale: 'en_US',
      },
    },
    {
      filename: 'foo.png',
      assetFamily: assetFamilyWithMainMediaLocalizableNotScopable,
      expected: {
        ...line,
        code: 'foo',
        locale: null,
      },
    },
    {
      filename: 'foo-ecommerce.png',
      assetFamily: assetFamilyWithMainMediaNotLocalizableScopable,
      expected: {
        ...line,
        code: 'foo',
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo-ecommerce',
      assetFamily: assetFamilyWithMainMediaNotLocalizableScopable,
      expected: {
        ...line,
        code: 'foo',
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo.png',
      assetFamily: assetFamilyWithMainMediaNotLocalizableScopable,
      expected: {
        ...line,
        code: 'foo',
        channel: null,
      },
    },
    {
      filename: 'foo-en_US-ecommerce.png',
      assetFamily: assetFamilyWithMainMediaLocalizableScopable,
      expected: {
        ...line,
        code: 'foo',
        locale: 'en_US',
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo-en_US-ecommerce',
      assetFamily: assetFamilyWithMainMediaLocalizableScopable,
      expected: {
        ...line,
        code: 'foo',
        locale: 'en_US',
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo--ecommerce.png',
      assetFamily: assetFamilyWithMainMediaLocalizableScopable,
      expected: {
        ...line,
        code: 'foo',
        locale: null,
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo.png',
      assetFamily: assetFamilyWithMainMediaLocalizableScopable,
      expected: {
        ...line,
        code: 'foo',
        locale: null,
        channel: null,
      },
    },
    {
      filename: 'Foo bar.png',
      assetFamily: assetFamilyWithMainMediaNotLocalizableNotScopable,
      expected: {
        ...line,
        code: 'foobar',
      },
    },
    {
      filename: 'foo.jpg.pdf.png',
      assetFamily: assetFamilyWithMainMediaNotLocalizableNotScopable,
      expected: {
        ...line,
        code: 'foo_jpg_pdf',
      },
    },
  ];

  test('I can create a line from a filename', () => {
    values.forEach((value) => {
      let result = createLineFromFilename(value.filename, value.assetFamily);
      expect(result).toMatchObject(value.expected);
      expect(result.filename).toBe(value.filename);
    });
  });
});

describe('src/Akeneo/AssetManager/front/application/asset-upload/utils/utils.ts -> addLines', () => {
  test('I can add new lines', () => {
    expect(addLines(['A', 'B', 'C'], ['D', 'E', 'F'])).toEqual(['D', 'E', 'F', 'A', 'B', 'C']);
    expect(addLines([], ['D', 'E', 'F'])).toEqual(['D', 'E', 'F']);
    expect(addLines(['A', 'B', 'C'], [])).toEqual(['A', 'B', 'C']);
  });
});

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

describe('src/Akeneo/AssetManager/front/application/asset-upload/utils/utils.ts -> createAssetsFromLines', () => {
  test('I can create Assets from a set of lines', () => {
    const lines = [
      createUploadedLineFromFilename('nice-en_US-ecommerce.jpg', assetFamilyWithMainMediaLocalizableScopable),
      createUploadedLineFromFilename('nice-en_US-ecommerce.jpg', assetFamilyWithMainMediaLocalizableScopable),
      createUploadedLineFromFilename('nice-fr_FR-ecommerce.jpg', assetFamilyWithMainMediaLocalizableScopable),
      createUploadedLineFromFilename('cool-fr_FR-ecommerce.jpg', assetFamilyWithMainMediaLocalizableScopable),
      createUploadedLineFromFilename('cool.jpg', assetFamilyWithMainMediaLocalizableScopable),
    ];
    expect(createAssetsFromLines(lines, assetFamilyWithMainMediaLocalizableScopable)).toEqual([
      {
        assetFamilyIdentifier: 'packshot',
        code: 'nice',
        labels: {},
        values: [
          {
            attribute: 'picture_fingerprint',
            channel: 'ecommerce',
            locale: 'en_US',
            data: {
              filePath: 'nice-en_US-ecommerce.jpg',
              originalFilename: 'nice-en_US-ecommerce.jpg',
            }
          },
          {
            attribute: 'picture_fingerprint',
            channel: 'ecommerce',
            locale: 'en_US',
            data: {
              filePath: 'nice-en_US-ecommerce.jpg',
              originalFilename: 'nice-en_US-ecommerce.jpg',
            }
          },
          {
            attribute: 'picture_fingerprint',
            channel: 'ecommerce',
            locale: 'fr_FR',
            data: {
              filePath: 'nice-fr_FR-ecommerce.jpg',
              originalFilename: 'nice-fr_FR-ecommerce.jpg',
            }
          },
        ],
      },
      {
        assetFamilyIdentifier: 'packshot',
        code: 'cool',
        labels: {},
        values: [
          {
            attribute: 'picture_fingerprint',
            channel: 'ecommerce',
            locale: 'fr_FR',
            data: {
              filePath: 'cool-fr_FR-ecommerce.jpg',
              originalFilename: 'cool-fr_FR-ecommerce.jpg',
            }
          },
          {
            attribute: 'picture_fingerprint',
            channel: null,
            locale: null,
            data: {
              filePath: 'cool.jpg',
              originalFilename: 'cool.jpg',
            }
          },
        ],
      },
    ]);
  });
});
