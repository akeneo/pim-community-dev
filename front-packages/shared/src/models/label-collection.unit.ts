import {getLabel, isLabelCollection, LabelCollection} from './label-collection';

const labelCollection: LabelCollection = {
  en_US: 'Name',
  fr_FR: 'Nom',
};

test('it can tell something is a label collection', () => {
  expect(isLabelCollection(labelCollection)).toBe(true);
  expect(isLabelCollection({})).toBe(true);

  expect(isLabelCollection('string')).toBe(false);
  expect(isLabelCollection(2)).toBe(false);
  expect(isLabelCollection(true)).toBe(false);
});

test('it can get the label from a label collection and use the fallback when not found', () => {
  expect(getLabel(labelCollection, 'en_US', 'name')).toBe('Name');
  expect(getLabel(labelCollection, 'fr_FR', 'name')).toBe('Nom');
  expect(getLabel(labelCollection, 'br_FR', 'name')).toBe('[name]');
});
