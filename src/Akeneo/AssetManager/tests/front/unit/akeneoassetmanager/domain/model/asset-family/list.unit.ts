import {
  createEmptyAssetFamilyListItem,
  createAssetFamilyListItemFromNormalized,
} from 'akeneoassetmanager/domain/model/asset-family/list';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';

const michelIdentifier = 'michel';
const michelLabels = {en_US: 'Michel'};
const didierCode = 'didier';
const didierLabels = {en_US: 'Didier'};

describe('akeneo > asset family > domain > model --- asset family', () => {
  test('I can create a new asset family with an identifier and labels', () => {
    expect(
      createAssetFamilyListItemFromNormalized({
        identifier: michelIdentifier,
        labels: michelLabels,
        image: createEmptyFile(),
      }).identifier
    ).toEqual(michelIdentifier);
  });

  test('I can get the collection of labels', () => {
    expect(
      createAssetFamilyListItemFromNormalized({
        identifier: michelIdentifier,
        labels: michelLabels,
        image: createEmptyFile(),
      }).labels
    ).toEqual(michelLabels);
  });

  test('I can create an empty asset family creation', () => {
    expect(createEmptyAssetFamilyListItem()).toEqual(
      createAssetFamilyListItemFromNormalized({identifier: '', labels: {}, image: null})
    );
  });
});
