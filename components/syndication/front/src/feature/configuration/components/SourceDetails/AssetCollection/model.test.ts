import {
  isAssetCollectionSource,
  AssetCollectionSource,
  isAssetCollectionSelection,
  isCollectionSeparator,
  isAssetCollectionMediaSelection,
  getDefaultAssetCollectionSelection,
  isDefaultAssetCollectionSelection,
  getDefaultAssetCollectionMediaSelection,
  isValidAssetCollectionMediaFileSelectionProperty,
} from './model';
import {AssetFamily} from '../../../models';
import {Channel} from '@akeneo-pim-community/shared';

test('it validates that something is a collection separator', () => {
  expect(isCollectionSeparator(',')).toBe(true);
});

test('it invalidates something when it is not a collection separator', () => {
  expect(isCollectionSeparator('blblb')).toBe(false);
});

test('it validates that something is a asset collection media file selection property', () => {
  expect(isValidAssetCollectionMediaFileSelectionProperty('file_key')).toBe(true);
  expect(isValidAssetCollectionMediaFileSelectionProperty('file_path')).toBe(true);
  expect(isValidAssetCollectionMediaFileSelectionProperty('original_filename')).toBe(true);
});

test('it invalidates something when it is not a asset collection media file selection property', () => {
  expect(isCollectionSeparator('fille_passe')).toBe(false);
});

describe('it validates that something is an asset collection selection', () => {
  test('it validates that something is an asset collection code selection', () => {
    expect(
      isAssetCollectionSelection({
        type: 'code',
        separator: ',',
      })
    ).toBe(true);
  });

  test('it validates that something is an asset collection label selection', () => {
    expect(
      isAssetCollectionSelection({
        type: 'label',
        locale: 'fr_FR',
        separator: '|',
      })
    ).toBe(true);
  });

  test('it validates that something is an asset collection media file selection', () => {
    expect(
      isAssetCollectionSelection({
        type: 'media_file',
        property: 'file_key',
        locale: null,
        channel: null,
        separator: ',',
      })
    ).toBe(true);
  });

  test('it validates that something is an asset collection media link selection', () => {
    expect(
      isAssetCollectionSelection({
        type: 'media_link',
        with_prefix_and_suffix: false,
        locale: null,
        channel: null,
        separator: ';',
      })
    ).toBe(true);
  });
});

describe('it invalidates something when it is not an asset collection selection', () => {
  test('it invalidates something when it is not any asset collection selection', () => {
    expect(
      isAssetCollectionSelection({
        type: 'feu',
        separator: ',',
      })
    ).toBe(false);
  });

  test('it invalidates something when it is not an asset collection code selection', () => {
    expect(
      isAssetCollectionSelection({
        type: 'code',
      })
    ).toBe(false);
  });

  test('it invalidates something when it is not an asset collection label selection', () => {
    expect(
      isAssetCollectionSelection({
        type: 'label',
        locale: null,
        separator: '|',
      })
    ).toBe(false);
  });

  test('it invalidates something when it is not an asset collection media file selection', () => {
    expect(
      isAssetCollectionSelection({
        type: 'media_file',
        property: 'bad_property',
        locale: null,
        channel: null,
        separator: ',',
      })
    ).toBe(false);
  });

  test('it invalidates something when it is not an asset collection media link selection', () => {
    expect(
      isAssetCollectionSelection({
        type: 'media_link',
        locale: null,
        channel: null,
        separator: ';',
      })
    ).toBe(false);
  });
});

describe('it validates that something is an asset collection media selection', () => {
  test('it validates that something is an asset collection media file selection', () => {
    expect(
      isAssetCollectionMediaSelection({
        type: 'media_file',
        property: 'file_key',
        separator: ',',
      })
    ).toBe(true);

    expect(
      isAssetCollectionMediaSelection({
        type: 'media_file',
        separator: ',',
      })
    ).toBe(false);
  });

  test('it validates that something is an asset collection media link selection', () => {
    expect(
      isAssetCollectionMediaSelection({
        type: 'media_link',
        with_prefix_and_suffix: true,
        separator: ',',
      })
    ).toBe(true);

    expect(
      isAssetCollectionMediaSelection({
        type: 'media_link',
        with_suffix_and_prefix: 'test',
        separator: ',',
      })
    ).toBe(false);
  });
});

test('it returns a default asset collection selection', () => {
  const target = {
    type: 'string' as const,
    name: 'asset',
    required: false,
  };
  expect(getDefaultAssetCollectionSelection(target)).toStrictEqual({
    type: 'code',
    separator: ',',
  });
});

test('it validates that something is a default asset collection selection', () => {
  expect(
    isDefaultAssetCollectionSelection({
      type: 'code',
      separator: ',',
    })
  ).toBe(true);

  expect(
    isDefaultAssetCollectionSelection({
      // @ts-expect-error invalid type
      type: 'label',
      locale: null,
      separator: ',',
    })
  ).toBe(false);
});

test('it returns a default asset collection media selection', () => {
  const channels: Channel[] = [
    {
      code: 'ecommerce',
      labels: {fr_FR: 'Ecommerce'},
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          region: 'US',
          language: 'en',
        },
        {
          code: 'fr_FR',
          label: 'Français',
          region: 'FR',
          language: 'fr',
        },
        {
          code: 'br_FR',
          label: 'Breton',
          region: 'bzh',
          language: 'br',
        },
      ],
      category_tree: '',
      conversion_units: [],
      currencies: [],
      meta: {
        created: '',
        form: '',
        id: 1,
        updated: '',
      },
    },
  ];
  const mediaFileAssetFamily: AssetFamily = {
    identifier: 'wallpapers',
    attribute_as_main_media: 'media_blablabla',
    attributes: [
      {
        identifier: 'media_blablabla',
        type: 'media_file',
        value_per_locale: false,
        value_per_channel: false,
      },
    ],
  };
  const mediaLinkAssetFamily: AssetFamily = {
    identifier: 'paints',
    attribute_as_main_media: 'link_blablabla',
    attributes: [
      {
        identifier: 'link_blablabla',
        type: 'media_link',
        value_per_locale: false,
        value_per_channel: true,
      },
    ],
  };
  const mediaFileScopableAndLocalizableAssetFamily: AssetFamily = {
    identifier: 'pokemons',
    attribute_as_main_media: 'media_blablabla2',
    attributes: [
      {
        identifier: 'media_blablabla2',
        type: 'media_file',
        value_per_locale: true,
        value_per_channel: true,
      },
    ],
  };
  const wrongFamily: AssetFamily = {
    identifier: 'wrong_family',
    attribute_as_main_media: 'media_blablabla',
    attributes: [
      {
        identifier: 'media_blablabla',
        type: 'wrong_type',
        value_per_locale: false,
        value_per_channel: false,
      },
    ],
  };

  expect(getDefaultAssetCollectionMediaSelection(mediaFileAssetFamily, channels)).toStrictEqual({
    type: 'media_file',
    locale: null,
    channel: null,
    property: 'file_key',
    separator: ',',
  });

  expect(getDefaultAssetCollectionMediaSelection(mediaLinkAssetFamily, channels)).toStrictEqual({
    type: 'media_link',
    locale: null,
    channel: 'ecommerce',
    with_prefix_and_suffix: false,
    separator: ',',
  });

  expect(getDefaultAssetCollectionMediaSelection(mediaFileScopableAndLocalizableAssetFamily, channels)).toStrictEqual({
    type: 'media_file',
    locale: 'en_US',
    channel: 'ecommerce',
    property: 'file_key',
    separator: ',',
  });

  expect(() => {
    getDefaultAssetCollectionMediaSelection(wrongFamily, channels);
  }).toThrow('Unknown attribute type : "wrong_type"');
});

const source: AssetCollectionSource = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {type: 'code', separator: ';'},
};

test('it validates that something is an asset collection source', () => {
  expect(isAssetCollectionSource(source)).toEqual(true);

  expect(
    isAssetCollectionSource({
      ...source,
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
      },
    })
  ).toEqual(true);

  expect(
    // @ts-expect-error invalid type
    isAssetCollectionSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
