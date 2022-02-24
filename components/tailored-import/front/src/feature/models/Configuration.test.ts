import {generateColumnName, getDefaultFileStructure} from './Configuration';

const mockUuid = 'uuid';

test('it generates column name', () => {
  expect(generateColumnName({uuid: mockUuid, index: 0, label: 'Sku'})).toEqual('Sku (A)');
  expect(generateColumnName({uuid: mockUuid, index: 25, label: 'Description'})).toEqual('Description (Z)');
  expect(generateColumnName({uuid: mockUuid, index: 26, label: 'Name'})).toEqual('Name (AA)');
  expect(generateColumnName({uuid: mockUuid, index: 51, label: 'Ref'})).toEqual('Ref (AZ)');
  expect(generateColumnName({uuid: mockUuid, index: 52, label: 'EAN'})).toEqual('EAN (BA)');
  expect(generateColumnName({uuid: mockUuid, index: 1023, label: 'Far far away column'})).toEqual(
    'Far far away column (AMJ)'
  );
  expect(generateColumnName({uuid: mockUuid, index: 702, label: 'Triple'})).toEqual('Triple (AAA)');
});

test('it can get the default file structure', () => {
  expect(getDefaultFileStructure()).toEqual({
    header_line: 0,
    first_column: 0,
    product_line: 1,
    sheet_name: null,
  });
});
