import {
  denormalizeLabelCollection,
  hasLabelInCollection,
  getLabelInCollection,
  setLabelInCollection,
} from 'akeneoassetmanager/domain/model/label-collection';

const rawLabels = {en_US: 'michel'};
describe('akeneo > asset family > domain > model --- label collection', () => {
  test('I can create a new label collection with some values', () => {
    expect(denormalizeLabelCollection({en_US: 'Michel'})).toEqual(denormalizeLabelCollection({en_US: 'Michel'}));
  });

  test('I cannot create a new label collection with a value other than a label collection', () => {
    expect(() => {
      denormalizeLabelCollection(12);
    }).toThrow('LabelCollection expect only values as {"en_US": "My label"} to be created');
    expect(() => {
      denormalizeLabelCollection({en_US: 24});
    }).toThrow('LabelCollection expect only values as {"en_US": "My label"} to be created');
  });

  test('I can test if it has a given label', () => {
    expect(hasLabelInCollection(rawLabels, 'en_US')).toBe(true);
    expect(hasLabelInCollection(rawLabels, 'fr_FR')).toBe(false);
    expect(hasLabelInCollection({123: 'michel'}, 'fr_FR')).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    expect(getLabelInCollection(denormalizeLabelCollection(rawLabels), 'en_US')).toBe('michel');
  });

  test('I can update a label for the given locale', () => {
    const updatedLabels = {en_US: 'michel', fr_FR: 'didier'};

    expect(setLabelInCollection({en_US: 'michel'}, 'fr_FR', 'didier')).toEqual(updatedLabels);
  });

  test('I can get the normalized labels', () => {
    const rawLabels = {en_US: 'michel'};

    expect(denormalizeLabelCollection({en_US: 'michel'})).toEqual(rawLabels);
  });
});
