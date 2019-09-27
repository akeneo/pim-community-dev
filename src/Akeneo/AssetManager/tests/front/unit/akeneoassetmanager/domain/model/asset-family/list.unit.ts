import {
  createAssetFamilyListItem,
  denormalizeAssetFamilyListItem,
  createEmptyAssetFamilyListItem,
} from 'akeneoassetmanager/domain/model/asset-family/list';
import {createIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';

const michelIdentifier = createIdentifier('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const didierCode = createIdentifier('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});

describe('akeneo > asset family > domain > model --- asset family', () => {
  test('I can create a new asset family with an identifier and labels', () => {
    expect(createAssetFamilyListItem(michelIdentifier, michelLabels, createEmptyFile()).getIdentifier()).toBe(
      michelIdentifier
    );
  });

  test('I cannot create a malformed asset family', () => {
    expect(() => {
      createAssetFamilyListItem(michelIdentifier);
    }).toThrow('AssetFamilyListItem expects a LabelCollection as labelCollection argument');
    expect(() => {
      createAssetFamilyListItem();
    }).toThrow('AssetFamilyListItem expects an Identifier as identifier argument');
    expect(() => {
      createAssetFamilyListItem(12);
    }).toThrow('AssetFamilyListItem expects an Identifier as identifier argument');
    expect(() => {
      createAssetFamilyListItem(michelIdentifier, 52);
    }).toThrow('AssetFamilyListItem expects a LabelCollection as labelCollection argument');
    expect(() => {
      createAssetFamilyListItem(michelIdentifier, 52, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('AssetFamilyListItem expects a LabelCollection as labelCollection argument');
    expect(() => {
      createAssetFamilyListItem(michelIdentifier, michelLabels, {
        filePath: 'my_path.png',
        originalFilename: 'path.png',
      });
    }).toThrow('AssetFamilyListItem expects a File as image argument');
  });

  test('I can compare two asset families', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createAssetFamilyListItem(didierCode, didierLabels, createEmptyFile()).equals(
        createAssetFamilyListItem(didierCode, didierLabels, createEmptyFile())
      )
    ).toBe(true);
    expect(
      createAssetFamilyListItem(didierCode, didierLabels, createEmptyFile()).equals(
        createAssetFamilyListItem(michelIdentifier, michelLabels, createEmptyFile())
      )
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(createAssetFamilyListItem(michelIdentifier, michelLabels, createEmptyFile()).getLabel('en_US')).toBe(
      'Michel'
    );
    expect(createAssetFamilyListItem(michelIdentifier, michelLabels, createEmptyFile()).getLabel('fr_FR')).toBe(
      '[michel]'
    );
    expect(createAssetFamilyListItem(michelIdentifier, michelLabels, createEmptyFile()).getLabel('fr_FR', false)).toBe(
      ''
    );
  });

  test('I can get the collection of labels', () => {
    expect(createAssetFamilyListItem(michelIdentifier, michelLabels, createEmptyFile()).getLabelCollection()).toBe(
      michelLabels
    );
  });

  test('I can create an empty asset family creation', () => {
    expect(createEmptyAssetFamilyListItem()).toEqual(
      denormalizeAssetFamilyListItem({identifier: '', labels: {}, image: null})
    );
  });

  test('I can normalize an asset family', () => {
    const michelAssetFamily = createAssetFamilyListItem(michelIdentifier, michelLabels, createEmptyFile());

    expect(michelAssetFamily.normalize()).toEqual({
      identifier: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
    });
  });

  test('I can normalize an asset family', () => {
    const michelAssetFamily = denormalizeAssetFamilyListItem({
      identifier: 'michel',
      labels: {
        en_US: 'Michel',
      },
      image: null,
    });

    expect(michelAssetFamily.normalize()).toEqual({
      identifier: 'michel',
      labels: {en_US: 'Michel'},
      image: null,
    });
  });
});
