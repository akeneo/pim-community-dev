import {
  createAssetFamilyListItem,
  denormalizeAssetFamilyListItem,
  createEmptyAssetFamilyListItem,
} from 'akeneoassetmanager/domain/model/asset-family/list';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';

const michelIdentifier = 'michel';
const michelLabels = {en_US: 'Michel'};
const didierCode = 'didier';
const didierLabels = {en_US: 'Didier'};

describe('akeneo > asset family > domain > model --- asset family', () => {
  test('I can create a new asset family with an identifier and labels', () => {
    expect(createAssetFamilyListItem(michelIdentifier, michelLabels, createEmptyFile()).getIdentifier()).toBe(
      michelIdentifier
    );
  });

  test('I cannot create a malformed asset family', () => {
    expect(() => {
      createAssetFamilyListItem(michelIdentifier, michelLabels, {
        filePath: 'my_path.png',
        originalFilename: 'path.png',
      });
    }).toThrow('AssetFamilyListItem expects a File as image argument');
  });

  test('I can compare two asset families', () => {
    const michelLabels = {en_US: 'Michel'};
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
