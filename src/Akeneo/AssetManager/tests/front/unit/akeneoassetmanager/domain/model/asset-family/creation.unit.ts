import {
  createEmptyAssetFamilyCreation,
  createAssetFamilyCreationFromNormalized,
  assetFamilyCreationAreEqual,
  getAssetFamilyCreationLabel,
} from 'akeneoassetmanager/domain/model/asset-family/creation';

const michelIdentifier = 'michel';
const michelLabels = {en_US: 'Michel'};
const didierCode = 'didier';
const didierLabels = {en_US: 'Didier'};

describe('akeneo > asset family > domain > model --- asset family', () => {
  test('I can create a new asset family with an code and labels', () => {
    expect(
      createAssetFamilyCreationFromNormalized({
        code: michelIdentifier,
        labels: michelLabels,
      }).code
    ).toEqual(michelIdentifier);
  });

  test('I can compare two asset families', () => {
    const michelLabels = {en_US: 'Michel'};
    expect(
      assetFamilyCreationAreEqual(
        createAssetFamilyCreationFromNormalized({
          code: didierCode,
          labels: didierLabels,
        }),
        createAssetFamilyCreationFromNormalized({
          code: didierCode,
          labels: didierLabels,
        })
      )
    ).toBe(true);
    expect(
      assetFamilyCreationAreEqual(
        createAssetFamilyCreationFromNormalized({
          code: didierCode,
          labels: didierLabels,
        }),
        createAssetFamilyCreationFromNormalized({
          code: michelIdentifier,
          labels: michelLabels,
        })
      )
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(
      getAssetFamilyCreationLabel(
        createAssetFamilyCreationFromNormalized({
          code: michelIdentifier,
          labels: michelLabels,
        }),
        'en_US'
      )
    ).toBe('Michel');
    expect(
      getAssetFamilyCreationLabel(
        createAssetFamilyCreationFromNormalized({
          code: michelIdentifier,
          labels: michelLabels,
        }),
        'fr_FR'
      )
    ).toBe('[michel]');
    expect(
      getAssetFamilyCreationLabel(
        createAssetFamilyCreationFromNormalized({
          code: michelIdentifier,
          labels: michelLabels,
        }),
        'fr_FR',
        false
      )
    ).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(
      createAssetFamilyCreationFromNormalized({
        code: michelIdentifier,
        labels: michelLabels,
      }).labels
    ).toEqual(michelLabels);
  });

  test('I can create an empty asset family creation', () => {
    expect(createEmptyAssetFamilyCreation()).toEqual({code: '', labels: {}});
  });
});
