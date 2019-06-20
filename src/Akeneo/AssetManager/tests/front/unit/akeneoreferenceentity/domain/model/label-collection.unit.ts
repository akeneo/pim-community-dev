import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';

describe('akeneo > reference entity > domain > model --- label collection', () => {
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
    expect(createLabelCollection({123: 'michel'}).hasLabel('fr_FR')).toBe(false);
  });

  test('I can get a label for the given locale', () => {
    const rawLabels = {en_US: 'michel'};
    expect(createLabelCollection(rawLabels).getLabel('en_US')).toBe('michel');
  });

  test('I cannot get a label for an unknown locale', () => {
    const rawLabels = {en_US: 'michel'};

    expect(() => {
      createLabelCollection({en_US: 'michel'}).getLabel('fr_FR');
    }).toThrow("The label for locale fr_FR doesn't exist");
  });

  test('I can update a label for the given locale', () => {
    const rawLabels = {en_US: 'michel'};
    const updatedLabels = createLabelCollection({en_US: 'michel', fr_FR: 'didier'});

    expect(createLabelCollection({en_US: 'michel'}).setLabel('fr_FR', 'didier')).toEqual(updatedLabels);
  });

  test('I can get the normalized labels', () => {
    const rawLabels = {en_US: 'michel'};

    expect(createLabelCollection({en_US: 'michel'}).normalize()).toEqual(rawLabels);
  });
});
