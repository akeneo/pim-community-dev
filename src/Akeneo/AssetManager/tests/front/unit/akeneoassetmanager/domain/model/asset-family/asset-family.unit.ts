import {
  createAssetFamily,
  denormalizeAssetFamily,
  createEmptyAssetFamily,
} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';

const michelIdentifier = 'michel';
const michelLabels = {en_US: 'Michel'};
const didierIdentifier = 'didier';
const didierLabels = {en_US: 'Didier'};
const attributeAsImage = 'name';
const attributeAsLabel = 'portrait';

describe('akeneo > asset family > domain > model --- asset family', () => {
  test('I can create a new asset family with an identifier and labels', () => {
    expect(
      createAssetFamily(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        attributeAsLabel,
        attributeAsImage
      ).getIdentifier()
    ).toBe(michelIdentifier);
  });

  test('I cannot create a malformed asset family', () => {
    expect(() => {
      createAssetFamily(michelIdentifier, michelLabels, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('AssetFamily expects a File as image argument');
  });

  test('I can compare two asset families', () => {
    const michelLabels = {en_US: 'Michel'};
    expect(
      createAssetFamily(
        didierIdentifier,
        didierLabels,
        createEmptyFile(),
        'name_michel_fingerprint',
        'image_michel_fingerprint'
      ).equals(
        createAssetFamily(
          didierIdentifier,
          didierLabels,
          createEmptyFile(),
          'name_michel_fingerprint',
          'image_michel_fingerprint'
        )
      )
    ).toBe(true);
    expect(
      createAssetFamily(
        didierIdentifier,
        didierLabels,
        createEmptyFile(),
        'name_michel_fingerprint',
        'image_michel_fingerprint'
      ).equals(
        createAssetFamily(
          michelIdentifier,
          michelLabels,
          createEmptyFile(),
          'name_michel_fingerprint',
          'image_michel_fingerprint'
        )
      )
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(
      createAssetFamily(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        'name_michel_fingerprint',
        'image_michel_fingerprint'
      ).getLabel('en_US')
    ).toBe('Michel');
    expect(
      createAssetFamily(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        'name_michel_fingerprint',
        'image_michel_fingerprint'
      ).getLabel('fr_FR')
    ).toBe('[michel]');
    expect(
      createAssetFamily(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        'name_michel_fingerprint',
        'image_michel_fingerprint'
      ).getLabel('fr_FR', false)
    ).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(
      createAssetFamily(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        'name_michel_fingerprint',
        'image_michel_fingerprint'
      ).getLabelCollection()
    ).toBe(michelLabels);
  });

  test('I can normalize an asset family', () => {
    const michelAssetFamily = createAssetFamily(
      michelIdentifier,
      michelLabels,
      createEmptyFile(),
      attributeAsImage,
      attributeAsLabel
    );

    expect(michelAssetFamily.normalize()).toEqual({
      identifier: 'michel',
      code: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
      attribute_as_image: 'portrait',
      attribute_as_label: 'name',
    });
  });

  test('I should be able to create an empty asset family', () => {
    const emptyAssetFamily = createEmptyAssetFamily();

    expect(emptyAssetFamily.normalize()).toEqual({
      identifier: '',
      code: '',
      labels: {},
      image: null,
      attribute_as_image: '',
      attribute_as_label: '',
    });
  });

  test('I can normalize an asset family', () => {
    const michelAssetFamily = denormalizeAssetFamily({
      identifier: 'michel',
      labels: {
        en_US: 'Michel',
      },
      image: null,
      attribute_as_image: 'portrait',
      attribute_as_label: 'name',
    });

    expect(michelAssetFamily.normalize()).toEqual({
      identifier: 'michel',
      code: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
      attribute_as_image: 'portrait',
      attribute_as_label: 'name',
    });
  });
});
