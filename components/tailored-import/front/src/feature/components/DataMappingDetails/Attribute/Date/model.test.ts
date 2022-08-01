import {isDateTarget, DateTarget, isDateFormat, formatDateFormat} from './model';
import {TextTarget} from '../Text/model';

test('it returns true if it is a date target', () => {
  const dateTarget: DateTarget = {
    code: 'response_time',
    type: 'attribute',
    attribute_type: 'pim_catalog_date',
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
    attribute_type: 'pim_catalog_text',
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

test('it can format a date format', () => {
  expect(formatDateFormat('yyyy-mm-dd')).toEqual('yyyy-mm-dd (1998-07-13)');
  expect(formatDateFormat('yyyy/mm/dd')).toEqual('yyyy/mm/dd (1998/07/13)');
  expect(formatDateFormat('yyyy.mm.dd')).toEqual('yyyy.mm.dd (1998.07.13)');
  expect(formatDateFormat('yy.m.dd')).toEqual('yy.m.dd (98.7.13)');
  expect(formatDateFormat('mm-dd-yyyy')).toEqual('mm-dd-yyyy (07-13-1998)');
  expect(formatDateFormat('mm/dd/yyyy')).toEqual('mm/dd/yyyy (07/13/1998)');
  expect(formatDateFormat('mm.dd.yyyy')).toEqual('mm.dd.yyyy (07.13.1998)');
  expect(formatDateFormat('dd-mm-yyyy')).toEqual('dd-mm-yyyy (13-07-1998)');
  expect(formatDateFormat('dd/mm/yyyy')).toEqual('dd/mm/yyyy (13/07/1998)');
  expect(formatDateFormat('dd.mm.yyyy')).toEqual('dd.mm.yyyy (13.07.1998)');
  expect(formatDateFormat('dd-mm-yy')).toEqual('dd-mm-yy (13-07-98)');
  expect(formatDateFormat('dd.mm.yy')).toEqual('dd.mm.yy (13.07.98)');
  expect(formatDateFormat('dd/mm/yy')).toEqual('dd/mm/yy (13/07/98)');
  expect(formatDateFormat('dd-m-yy')).toEqual('dd-m-yy (13-7-98)');
  expect(formatDateFormat('dd.m.yy')).toEqual('dd.m.yy (13.7.98)');
  expect(formatDateFormat('dd/m/yy')).toEqual('dd/m/yy (13/7/98)');
});
