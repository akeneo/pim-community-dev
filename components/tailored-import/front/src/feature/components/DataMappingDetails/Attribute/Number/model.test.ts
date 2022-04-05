import {isNumberTarget, NumberTarget} from './model';
import {TextTarget} from '../Text/model';

test('it returns true if it is a number target', () => {
  const numberTarget: NumberTarget = {
    code: 'response_time',
    type: 'attribute',
    locale: null,
    channel: null,
    source_parameter: {decimal_separator: ','},
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isNumberTarget(numberTarget)).toEqual(true);
});

test('it returns false if it is not a number target', () => {
  const textTarget: TextTarget = {
    code: 'name',
    type: 'attribute',
    locale: null,
    channel: null,
    source_parameter: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isNumberTarget(textTarget)).toEqual(false);
});
