import {CategoriesTarget, isCategoriesTarget} from './model';
import {NumberTarget} from '../../Attribute';

test('it returns true if it is a categories target', () => {
  const simpleSelectTarget: CategoriesTarget = {
    code: 'categories',
    type: 'property',
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isCategoriesTarget(simpleSelectTarget)).toEqual(true);
});

test('it returns false if it is not a categories target', () => {
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

  expect(isCategoriesTarget(numberTarget)).toEqual(false);
});
