import {isDateTarget, DateTarget, isDateFormat} from './model';
import {TextTarget} from '../Text/model';

test('it returns true if it is a date target', () => {
  const dateTarget: DateTarget = {
    code: 'response_time',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: {date_format: 'mm/dd/yyyy'},
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isDateTarget(dateTarget)).toEqual(true);
});

test('it returns false if it is not a date target', () => {
  const textTarget: TextTarget = {
    code: 'name',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isDateTarget(textTarget)).toEqual(false);
});

test('it can tell if something is a date format', () => {
  expect(isDateFormat('mm/dd/yyyy')).toEqual(true);
  expect(isDateFormat('mm-dd-yyyy')).toEqual(true);
  expect(isDateFormat('.')).toEqual(false);
  expect(isDateFormat('')).toEqual(false);
  expect(isDateFormat('')).toEqual(false);
});
