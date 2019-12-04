import {
  createAssetFamilyFromNormalized,
  createEmptyAssetFamily,
  getAssetFamilyLabel,
  assetFamilyAreEqual,
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
      transformations: '[]',
    });
  });
});
