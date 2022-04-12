import {isNumberTarget, NumberTarget} from './model';
import {TextTarget} from '../Text/model';

test('it returns true if it is a number target', () => {
  const numberTarget: NumberTarget = {
    code: 'response_time',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: {decimal_separator: ','},
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
    source_configuration: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isNumberTarget(textTarget)).toEqual(false);
});
