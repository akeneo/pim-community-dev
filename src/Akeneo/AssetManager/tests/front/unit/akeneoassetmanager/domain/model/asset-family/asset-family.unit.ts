import {createAssetFamily, denormalizeAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {createIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {createIdentifier as createAttributeIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';

const michelIdentifier = createIdentifier('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const didierIdentifier = createIdentifier('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});
const attributeAsImage = createAttributeIdentifier('name');
const attributeAsLabel = createAttributeIdentifier('portrait');

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
      createAssetFamily(michelIdentifier);
    }).toThrow('AssetFamily expects a LabelCollection as labelCollection argument');
    expect(() => {
      createAssetFamily();
    }).toThrow('AssetFamily expects an AssetFamilyIdentifier as identifier argument');
    expect(() => {
      createAssetFamily(12);
    }).toThrow('AssetFamily expects an AssetFamilyIdentifier as identifier argument');
    expect(() => {
      createAssetFamily(michelIdentifier, 52);
    }).toThrow('AssetFamily expects a LabelCollection as labelCollection argument');
    expect(() => {
      createAssetFamily(michelIdentifier, 52, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('AssetFamily expects a LabelCollection as labelCollection argument');
    expect(() => {
      createAssetFamily(michelIdentifier, michelLabels, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('AssetFamily expects a File as image argument');
    expect(() => {
      createAssetFamily(michelIdentifier, michelLabels, createEmptyFile());
    }).toThrow('AssetFamily expects a AttributeIdentifier as attributeAsLabel argument');
    expect(() => {
      createAssetFamily(michelIdentifier, michelLabels, createEmptyFile(), attributeAsLabel);
    }).toThrow('AssetFamily expects a AttributeIdentifier as attributeAsImage argument');
  });

  test('I can compare two asset families', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createAssetFamily(
        didierIdentifier,
        didierLabels,
        createEmptyFile(),
        createAttributeIdentifier('name_michel_fingerprint'),
        createAttributeIdentifier('image_michel_fingerprint')
      ).equals(
        createAssetFamily(
          didierIdentifier,
          didierLabels,
          createEmptyFile(),
          createAttributeIdentifier('name_michel_fingerprint'),
          createAttributeIdentifier('image_michel_fingerprint')
        )
      )
    ).toBe(true);
    expect(
      createAssetFamily(
        didierIdentifier,
        didierLabels,
        createEmptyFile(),
        createAttributeIdentifier('name_michel_fingerprint'),
        createAttributeIdentifier('image_michel_fingerprint')
      ).equals(
        createAssetFamily(
          michelIdentifier,
          michelLabels,
          createEmptyFile(),
          createAttributeIdentifier('name_michel_fingerprint'),
          createAttributeIdentifier('image_michel_fingerprint')
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
        createAttributeIdentifier('name_michel_fingerprint'),
        createAttributeIdentifier('image_michel_fingerprint')
      ).getLabel('en_US')
    ).toBe('Michel');
    expect(
      createAssetFamily(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        createAttributeIdentifier('name_michel_fingerprint'),
        createAttributeIdentifier('image_michel_fingerprint')
      ).getLabel('fr_FR')
    ).toBe('[michel]');
    expect(
      createAssetFamily(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        createAttributeIdentifier('name_michel_fingerprint'),
        createAttributeIdentifier('image_michel_fingerprint')
      ).getLabel('fr_FR', false)
    ).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(
      createAssetFamily(
        michelIdentifier,
        michelLabels,
        createEmptyFile(),
        createAttributeIdentifier('name_michel_fingerprint'),
        createAttributeIdentifier('image_michel_fingerprint')
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
