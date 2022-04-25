import {Column, filterColumnsByUuids, generateColumnName} from './Column';

test('it generates column name', () => {
  expect(generateColumnName(0, 'Sku')).toEqual('Sku (A)');
  expect(generateColumnName(25, 'Description')).toEqual('Description (Z)');
  expect(generateColumnName(26, 'Name')).toEqual('Name (AA)');
  expect(generateColumnName(51, 'Ref')).toEqual('Ref (AZ)');
  expect(generateColumnName(52, 'EAN')).toEqual('EAN (BA)');
  expect(generateColumnName(1023, 'Far far away column')).toEqual('Far far away column (AMJ)');
  expect(generateColumnName(702, 'Triple')).toEqual('Triple (AAA)');
});

test('it returns column from uuid', () => {
  const columns: Column[] = [
    {
      uuid: 'c33e67f4-a6f7-4950-81c1-1800a956b88f',
      label: 'name',
      index: 1,
    },
    {
      uuid: 'aaaaa5ca-e532-45bd-801b-65a7966a8f18',
      label: 'description',
      index: 2,
    },
  ];

  expect(filterColumnsByUuids(columns, ['c33e67f4-a6f7-4950-81c1-1800a956b88f'])).toEqual([columns[0]]);
  expect(
    filterColumnsByUuids(columns, ['c33e67f4-a6f7-4950-81c1-1800a956b88f', 'aaaaa5ca-e532-45bd-801b-65a7966a8f18'])
  ).toEqual(columns);
  expect(filterColumnsByUuids(columns, ['unknown_uuid'])).toEqual([]);
});
