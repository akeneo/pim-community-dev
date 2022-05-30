import {FamilyTarget, isFamilyTarget} from './model';
import {NumberTarget} from '../../Attribute';

test('it returns true if it is a family target', () => {
  const familyTarget: FamilyTarget = {
    code: 'family',
    type: 'property',
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isFamilyTarget(familyTarget)).toEqual(true);
});

test('it returns false if it is not a family target', () => {
  const numberTarget: NumberTarget = {
    code: 'name',
    type: 'attribute',
    attribute_type: 'pim_catalog_number',
    locale: null,
    channel: null,
    source_configuration: {decimal_separator: ','},
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isFamilyTarget(numberTarget)).toEqual(false);
});
