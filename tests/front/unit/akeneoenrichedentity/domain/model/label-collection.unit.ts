import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

describe('akeneo > enriched entity > domain > model --- label collection', () => {
  test('I can create a new label collection with some values', () => {
    expect(createLabelCollection({en_US: 'Michel'})).toEqual(createLabelCollection({en_US: 'Michel'}));
  });

  test('I cannot create a new label collection with a value other than a label collection', () => {
    expect(() => {
      createLabelCollection(12);
    }).toThrow('LabelCollection expect only values as {"en_US": "My label"} to be created');
    expect(() => {
      createLabelCollection({en_US: 24});
    }).toThrow('LabelCollection expect only values as {"en_US": "My label"} to be created');
  });

  test('I can test if it has a given label', () => {
    const rawLabels = {en_US: 'michel'};
    expect(createLabelCollection(rawLabels).hasLabel('en_US')).toBe(true);
    expect(createLabelCollection(rawLabels).hasLabel('fr_FR')).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    const rawLabels = {en_US: 'michel'};
    expect(createLabelCollection(rawLabels).getLabel('en_US')).toBe('michel');
  });

  test('I can get a label for the given locale', () => {
    const rawLabels = {en_US: 'michel'};

    expect(() => {
      createLabelCollection({en_US: 'michel'}).getLabel('fr_FR');
    }).toThrow("The label for locale fr_FR doesn't exist");
  });

  test('I can get the normalized labels', () => {
    const rawLabels = {en_US: 'michel'};

    expect(createLabelCollection({en_US: 'michel'}).getLabels()).toEqual(rawLabels);
  });
});
