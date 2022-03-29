import {generateColumnName, getDefaultFileStructure, findColumnByUuid, Column} from './Configuration';

test('it generates column name', () => {
  expect(generateColumnName(0, 'Sku')).toEqual('Sku (A)');
  expect(generateColumnName(25, 'Description')).toEqual('Description (Z)');
  expect(generateColumnName(26, 'Name')).toEqual('Name (AA)');
  expect(generateColumnName(51, 'Ref')).toEqual('Ref (AZ)');
  expect(generateColumnName(52, 'EAN')).toEqual('EAN (BA)');
  expect(generateColumnName(1023, 'Far far away column')).toEqual('Far far away column (AMJ)');
  expect(generateColumnName(702, 'Triple')).toEqual('Triple (AAA)');
});

test('it can get the default file structure', () => {
  expect(getDefaultFileStructure()).toEqual({
    unique_identifier_column: 0,
    header_row: 1,
    first_column: 0,
    first_product_row: 2,
    sheet_name: null,
  });
});

test('it returns column from uuid', () => {
  let colums = [
    {
      uuid: 'c33e67f4-a6f7-4950-81c1-1800a956b88f',
      label: 'name',
      index: 1,
    } as Column,
    {
      uuid: 'cb5bb5ca-e532-45bd-801b-65a7966a8f18',
      label: 'description',
      index: 2,
    } as Column,
  ];

  const result = findColumnByUuid(colums, 'c33e67f4-a6f7-4950-81c1-1800a956b88f');
  expect(result).toEqual(colums[0]);
});

test("it return null when column is not find", () => {
  let colums = [
    {
      uuid: 'c33e67f4-a6f7-4950-81c1-1800a956b88f',
      label: 'name',
      index: 1,
    } as Column,
    {
      uuid: 'cb5bb5ca-e532-45bd-801b-65a7966a8f18',
      label: 'description',
      index: 2,
    } as Column,
  ];

  const result = findColumnByUuid(colums, '885bbc55-ed08-4392-8096-4d7fed9f0ca9');
  expect(result).toBeNull();
});
