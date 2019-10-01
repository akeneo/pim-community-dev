import {
  createEmptyAssetFamilyCreation,
  denormalizeAssetFamilyCreation,
} from 'akeneoassetmanager/domain/model/asset-family/creation';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';

const michelLabels = createLabelCollection({en_US: 'Michel'});

describe('akeneo > asset family > domain > model --- asset family', () => {
  test('I can compare two asset families', () => {
    expect(
      denormalizeAssetFamilyCreation({
        code: 'didier',
        labels: {en_US: 'Didier'},
      }).equals(
        denormalizeAssetFamilyCreation({
          code: 'didier',
          labels: {en_US: 'Didier'},
        })
      )
    ).toBe(true);
    expect(
      denormalizeAssetFamilyCreation({
        code: 'didier',
        labels: {en_US: 'Didier'},
      }).equals(
        denormalizeAssetFamilyCreation({
          code: 'michel',
          labels: {en_US: 'Michel'},
        })
      )
    ).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(
      denormalizeAssetFamilyCreation({
        code: 'michel',
        labels: {en_US: 'Michel'},
      }).getLabel('en_US')
    ).toBe('Michel');
    expect(
      denormalizeAssetFamilyCreation({
        code: 'michel',
        labels: {en_US: 'Michel'},
      }).getLabel('fr_FR')
    ).toBe('[michel]');
    expect(
      denormalizeAssetFamilyCreation({
        code: 'michel',
        labels: {en_US: 'Michel'},
      }).getLabel('fr_FR', false)
    ).toBe('');
  });

  test('I can get the collection of labels', () => {
    expect(
      denormalizeAssetFamilyCreation({
        code: 'michel',
        labels: {en_US: 'Michel'},
      }).getLabelCollection()
    ).toEqual(michelLabels);
  });

  test('I can create an empty asset family creation', () => {
    expect(createEmptyAssetFamilyCreation()).toEqual(denormalizeAssetFamilyCreation({code: '', labels: {}}));
  });

  test('I can normalize an asset family creation', () => {
    const michelAssetFamily = denormalizeAssetFamilyCreation({
      code: 'michel',
      labels: {en_US: 'Michel'},
    });

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
