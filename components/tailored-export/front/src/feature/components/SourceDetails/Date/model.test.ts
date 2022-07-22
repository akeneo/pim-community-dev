import {Source} from '../../../models';
import {formatDateFormat, isDateSource} from './model';

const source: Source = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {format: 'yyyy-mm-dd'},
};

test('it validates that something is a date source', () => {
  expect(isDateSource(source)).toEqual(true);

  expect(
    isDateSource({
      ...source,
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
      },
    })
  ).toEqual(true);

  expect(
    // @ts-expect-error invalid operations
    isDateSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
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
