import {
  createAssetFamilyCreation,
  createEmptyAssetFamilyCreation,
  denormalizeAssetFamilyCreation,
} from 'akeneoassetmanager/domain/model/asset-family/creation';
import {createCode} from 'akeneoassetmanager/domain/model/code';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';

const michelCode = createCode('michel');
const michelLabels = createLabelCollection({en_US: 'Michel'});
const didierCode = createCode('didier');
const didierLabels = createLabelCollection({en_US: 'Didier'});

describe('akeneo > asset family > domain > model --- asset family', () => {
  test('I can create a new asset family creation with an identifier and labels', () => {
    expect(createAssetFamilyCreation(michelCode, michelLabels).getCode()).toBe(michelCode);
  });

  test('I cannot create a malformed asset family creation', () => {
    expect(() => {
      createAssetFamilyCreation(michelCode);
    }).toThrow('AssetFamilyCreation expects a LabelCollection as labelCollection argument');
    expect(() => {
      createAssetFamilyCreation();
    }).toThrow('AssetFamilyCreation expects a Code as code argument');
    expect(() => {
      createAssetFamilyCreation(12);
    }).toThrow('AssetFamilyCreation expects a Code as code argument');
    expect(() => {
      createAssetFamilyCreation(michelCode, 52);
    }).toThrow('AssetFamilyCreation expects a LabelCollection as labelCollection argument');
    expect(() => {
      createAssetFamilyCreation(michelCode, 52, {filePath: 'my_path.png', originalFilename: 'path.png'});
    }).toThrow('AssetFamilyCreation expects a LabelCollection as labelCollection argument');
  });

  test('I can compare two asset families', () => {
    const michelLabels = createLabelCollection({en_US: 'Michel'});
    expect(
      createAssetFamilyCreation(didierCode, didierLabels).equals(createAssetFamilyCreation(didierCode, didierLabels))
    ).toBe(true);
    expect(
      createAssetFamilyCreation(didierCode, didierLabels).equals(createAssetFamilyCreation(michelCode, michelLabels))
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(createAssetFamilyCreation(michelCode, michelLabels).getLabel('en_US')).toBe('Michel');
    expect(createAssetFamilyCreation(michelCode, michelLabels).getLabel('fr_FR')).toBe('[michel]');
    expect(createAssetFamilyCreation(michelCode, michelLabels).getLabel('fr_FR', false)).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(createAssetFamilyCreation(michelCode, michelLabels).getLabelCollection()).toBe(michelLabels);
  });

  test('I can create an empty asset family creation', () => {
    expect(createEmptyAssetFamilyCreation()).toEqual(denormalizeAssetFamilyCreation({code: '', labels: {}}));
  });

  test('I can normalize an asset family creation', () => {
    const michelAssetFamily = createAssetFamilyCreation(michelCode, michelLabels);

    expect(michelAssetFamily.normalize()).toEqual({
      code: 'michel',
      labels: {en_US: 'Michel'},
    });
  });

  test('I can normalize an asset family creation', () => {
    const michelAssetFamily = denormalizeAssetFamilyCreation({
      code: 'michel',
      labels: {
        en_US: 'Michel',
      },
    });

    expect(michelAssetFamily.normalize()).toEqual({
      code: 'michel',
      labels: {en_US: 'Michel'},
    });
  });
});
