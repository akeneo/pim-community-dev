import {getDefaultFileStructure} from './Configuration';

test('it can get the default file structure', () => {
  expect(getDefaultFileStructure()).toEqual({
    unique_identifier_column: 0,
    header_row: 1,
    first_column: 0,
    first_product_row: 2,
    sheet_name: null,
  });
});
