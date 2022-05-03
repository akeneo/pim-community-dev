import {isMultiSelectTarget, MultiSelectTarget} from './model';
import {NumberTarget} from '../Number/model';

test('it returns true if it is a multi select target', () => {
  const multiSelectTarget: MultiSelectTarget = {
    code: 'response_time',
    type: 'attribute',
    attribute_type: 'pim_catalog_multiselect',
    locale: null,
    channel: null,
    source_configuration: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isMultiSelectTarget(multiSelectTarget)).toEqual(true);
});

test('it returns false if it is not a multi select target', () => {
  const numberTarget: NumberTarget = {
    code: 'pieces_count',
    type: 'attribute',
    attribute_type: 'pim_catalog_number',
    locale: null,
    channel: null,
    source_configuration: {decimal_separator: ','},
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isMultiSelectTarget(numberTarget)).toEqual(false);
});
