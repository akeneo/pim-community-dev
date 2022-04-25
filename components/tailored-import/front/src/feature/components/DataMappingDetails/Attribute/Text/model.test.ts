import {isTextTarget, TextTarget} from './model';
import {NumberTarget} from '../Number/model';

test('it returns true if it is a text target', () => {
  const numberTarget: TextTarget = {
    code: 'response_time',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isTextTarget(numberTarget)).toEqual(true);
});

test('it returns false if it is not a number target', () => {
  const textTarget: NumberTarget = {
    code: 'name',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: {decimal_separator: ','},
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isTextTarget(textTarget)).toEqual(false);
});
