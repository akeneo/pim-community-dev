import {
  createAssetFamilyFromNormalized,
  createEmptyAssetFamily,
  getAssetFamilyLabel,
  assetFamilyAreEqual,
  getAttributeAsMainMedia,
} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';

const michelIdentifier = 'michel';
const michelLabels = {en_US: 'Michel'};
const didierIdentifier = 'didier';
const didierLabels = {en_US: 'Didier'};
const attributeAsMainMedia = 'name';
const attributeAsLabel = 'portrait';

describe('akeneo > asset family > domain > model --- asset family', () => {
  test('I can create a new asset family with an identifier and labels', () => {
    expect(
      createAssetFamilyFromNormalized({
        identifier: michelIdentifier,
        code: michelIdentifier,
        labels: michelLabels,
        image: createEmptyFile(),
        attribute_as_label: attributeAsLabel,
        attribute_as_main_media: attributeAsMainMedia,
      }).identifier
    ).toBe(michelIdentifier);
  });

  test('I can compare two asset families', () => {
    const michelLabels = {en_US: 'Michel'};
    expect(
      assetFamilyAreEqual(
        createAssetFamilyFromNormalized({
          identifier: didierIdentifier,
          code: didierIdentifier,
          labels: michelLabels,
          image: createEmptyFile(),
          attribute_as_label: 'name_michel_fingerprint',
          attribute_as_main_media: 'image_michel_fingerprint',
        }),
        createAssetFamilyFromNormalized({
          identifier: didierIdentifier,
          code: didierIdentifier,
          labels: didierLabels,
          image: createEmptyFile(),
          attribute_as_label: 'name_michel_fingerprint',
          attribute_as_main_media: 'image_michel_fingerprint',
        })
      )
    ).toBe(true);
    expect(
      assetFamilyAreEqual(
        createAssetFamilyFromNormalized({
          identifier: didierIdentifier,
          code: didierIdentifier,
          labels: michelLabels,
          image: createEmptyFile(),
          attribute_as_label: 'name_michel_fingerprint',
          attribute_as_main_media: 'image_michel_fingerprint',
        }),
        createAssetFamilyFromNormalized({
          identifier: michelIdentifier,
          code: michelIdentifier,
          labels: michelLabels,
          image: createEmptyFile(),
          attribute_as_label: 'name_michel_fingerprint',
          attribute_as_main_media: 'image_michel_fingerprint',
        })
      )
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(
      getAssetFamilyLabel(
        createAssetFamilyFromNormalized({
          identifier: michelIdentifier,
          code: michelIdentifier,
          labels: michelLabels,
          image: createEmptyFile(),
          attribute_as_label: 'name_michel_fingerprint',
          attribute_as_main_media: 'image_michel_fingerprint',
        }),
        'en_US'
      )
    ).toBe('Michel');
    expect(
      getAssetFamilyLabel(
        createAssetFamilyFromNormalized({
          identifier: michelIdentifier,
          code: michelIdentifier,
          labels: michelLabels,
          image: createEmptyFile(),
          attribute_as_label: 'name_michel_fingerprint',
          attribute_as_main_media: 'image_michel_fingerprint',
        }),
        'fr_FR'
      )
    ).toBe('[michel]');
    expect(
      getAssetFamilyLabel(
        createAssetFamilyFromNormalized({
          identifier: michelIdentifier,
          code: michelIdentifier,
          labels: michelLabels,
          image: createEmptyFile(),
          attribute_as_label: 'name_michel_fingerprint',
          attribute_as_main_media: 'image_michel_fingerprint',
        }),
        'fr_FR',
        false
      )
    ).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(
      createAssetFamilyFromNormalized({
        identifier: michelIdentifier,
        code: michelIdentifier,
        labels: michelLabels,
        image: createEmptyFile(),
        attribute_as_label: 'name_michel_fingerprint',
        attribute_as_main_media: 'image_michel_fingerprint',
      }).labels
    ).toEqual(michelLabels);
  });

  test('I should be able to create an empty asset family', () => {
    const emptyAssetFamily = createEmptyAssetFamily();

    expect(emptyAssetFamily).toEqual({
      identifier: '',
      code: '',
      labels: {},
      image: null,
      attributeAsMainMedia: '',
      attributeAsLabel: '',
      attributes: [],
      transformations: [],
      productLinkRules: '[]',
      namingConvention: '{}',
    });
  });

  test('I should be able to get the attribute as main media', () => {
    const assetFamily = createAssetFamilyFromNormalized({
      identifier: michelIdentifier,
      code: michelIdentifier,
      labels: michelLabels,
      image: createEmptyFile(),
      attribute_as_label: 'name_michel_fingerprint',
      attribute_as_main_media: 'image_michel_fingerprint',
      attributes: [
        {
          identifier: 'second_image',
        },
        {
          identifier: 'image_michel_fingerprint',
        },
      ],
    });

    expect(getAttributeAsMainMedia(assetFamily)).toEqual({
      identifier: 'image_michel_fingerprint',
    });
  });

  test('I should get an error if I try to get and attribute as main media that does not exist', () => {
    const assetFamily = createAssetFamilyFromNormalized({
      identifier: michelIdentifier,
      code: michelIdentifier,
      labels: michelLabels,
      image: createEmptyFile(),
      attribute_as_label: 'name_michel_fingerprint',
      attribute_as_main_media: 'image_michel_fingerprint',
      attributes: [
        {
          identifier: 'second_image',
        },
        {
          identifier: 'label',
        },
      ],
    });

    expect(() => getAttributeAsMainMedia(assetFamily)).toThrowError(
      'The AssetFamily must have an attribute as main media'
    );
  });
});
