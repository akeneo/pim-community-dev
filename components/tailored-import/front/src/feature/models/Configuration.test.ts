import {getDefaultFileStructure, isDefaultFileStructure} from './Configuration';

test('it can get the default file structure', () => {
  expect(getDefaultFileStructure()).toEqual({
    unique_identifier_column: 0,
    header_row: 1,
    first_column: 0,
    first_product_row: 2,
    sheet_name: null,
  });
});

test('it can tell if a file structure is the default one', () => {
  expect(isDefaultFileStructure(getDefaultFileStructure())).toBe(true);
  expect(
    isDefaultFileStructure({
      unique_identifier_column: 0,
      header_row: 1,
      first_column: 0,
      first_product_row: 2,
      sheet_name: 'pere fouras',
    })
  ).toBe(false);
  expect(
    isDefaultFileStructure({
      unique_identifier_column: 0,
      header_row: 2,
      first_column: 0,
      first_product_row: 2,
      sheet_name: 'pere fouras',
    })
  ).toBe(false);
});
