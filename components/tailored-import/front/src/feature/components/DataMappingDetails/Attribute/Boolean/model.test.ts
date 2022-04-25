import {isBooleanTarget, BooleanTarget} from './model';
import {NumberTarget} from '../Number/model';

test('it returns true if it is a boolean target', () => {
  const booleanTarget: BooleanTarget = {
    code: 'response_time',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isBooleanTarget(booleanTarget)).toEqual(true);
});

test('it returns false if it is not a boolean target', () => {
  const numberTarget: NumberTarget = {
    code: 'name',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: {decimal_separator: ','},
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isBooleanTarget(numberTarget)).toEqual(false);
});
