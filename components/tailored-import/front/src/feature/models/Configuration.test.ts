import {generateColumnName, getDefaultFileStructure} from './Configuration';

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
    column_identifier_position: 0,
    header_line: 1,
    first_column: 0,
    product_line: 2,
    sheet_name: null,
  });
});
