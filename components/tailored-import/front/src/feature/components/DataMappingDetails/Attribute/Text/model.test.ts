import {isTextTarget, TextTarget} from './model';
import {NumberTarget} from '../Number/model';

test('it returns true if it is a text target', () => {
  const textTarget: TextTarget = {
    code: 'response_time',
    type: 'attribute',
    attribute_type: 'pim_catalog_text',
    locale: null,
    channel: null,
    source_configuration: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isTextTarget(textTarget)).toEqual(true);
});

test('it returns false if it is not a number target', () => {
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

  expect(isTextTarget(numberTarget)).toEqual(false);
});
