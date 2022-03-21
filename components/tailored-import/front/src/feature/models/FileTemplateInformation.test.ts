import {FileTemplateInformation, getRowAtPosition, getRowsFromPosition} from './FileTemplateInformation';

const fileTemplateInformation: FileTemplateInformation = {
  sheet_names: ['first sheet', 'second sheet', 'third sheet'],
  rows: [
    ['', '', '', '', '', '', ''],
    ['', 'Sku', 'Name', 'Price', 'Enabled', 'Release date', 'Price with tax'],
    ['', 'ref1', 'Produit 1', '$13.87 ', 'TRUE', '3/22/2022', '14.4'],
    ['', 'ref2', 'Produit 2', '$12.00 ', 'FALSE', '5/23/2022', '16.644'],
  ],
  column_count: 7,
};

test('it returns row at a specific position', () => {
  expect(getRowAtPosition(fileTemplateInformation, 0)).toEqual(['', '', '', '', '', '', '']);
  expect(getRowAtPosition(fileTemplateInformation, 1)).toEqual(['', '', '', '', '', '', '']);
  expect(getRowAtPosition(fileTemplateInformation, 2)).toEqual([
    '',
    'Sku',
    'Name',
    'Price',
    'Enabled',
    'Release date',
    'Price with tax',
  ]);
  expect(getRowAtPosition(fileTemplateInformation, 5)).toEqual(['', '', '', '', '', '', '']);

  expect(getRowAtPosition(fileTemplateInformation, 0, 1)).toEqual(['', '', '', '', '', '']);
  expect(getRowAtPosition(fileTemplateInformation, 1, 1)).toEqual(['', '', '', '', '', '']);
  expect(getRowAtPosition(fileTemplateInformation, 2, 1)).toEqual([
    'Sku',
    'Name',
    'Price',
    'Enabled',
    'Release date',
    'Price with tax',
  ]);
  expect(getRowAtPosition(fileTemplateInformation, 5, 1)).toEqual(['', '', '', '', '', '']);

  expect(getRowAtPosition(fileTemplateInformation, 0, 2)).toEqual(['', '', '', '', '']);
  expect(getRowAtPosition(fileTemplateInformation, 1, 2)).toEqual(['', '', '', '', '']);
  expect(getRowAtPosition(fileTemplateInformation, 2, 2)).toEqual([
    'Name',
    'Price',
    'Enabled',
    'Release date',
    'Price with tax',
  ]);
  expect(getRowAtPosition(fileTemplateInformation, 5, 2)).toEqual(['', '', '', '', '']);
});

test('it returns empty row when position is invalid', () => {
  expect(getRowAtPosition(fileTemplateInformation, -1)).toEqual(['', '', '', '', '', '', '']);
  expect(getRowAtPosition(fileTemplateInformation, -1, -1)).toEqual(['', '', '', '', '', '', '']);
  expect(getRowAtPosition(fileTemplateInformation, 2, -1)).toEqual(['', '', '', '', '', '', '']);
});

test('it returns rows at a specific position', () => {
  expect(getRowsFromPosition(fileTemplateInformation, 1)).toEqual([
    ['', '', '', '', '', '', ''],
    ['', 'Sku', 'Name', 'Price', 'Enabled', 'Release date', 'Price with tax'],
    ['', 'ref1', 'Produit 1', '$13.87 ', 'TRUE', '3/22/2022', '14.4'],
    ['', 'ref2', 'Produit 2', '$12.00 ', 'FALSE', '5/23/2022', '16.644'],
  ]);

  expect(getRowsFromPosition(fileTemplateInformation, 2)).toEqual([
    ['', 'Sku', 'Name', 'Price', 'Enabled', 'Release date', 'Price with tax'],
    ['', 'ref1', 'Produit 1', '$13.87 ', 'TRUE', '3/22/2022', '14.4'],
    ['', 'ref2', 'Produit 2', '$12.00 ', 'FALSE', '5/23/2022', '16.644'],
  ]);

  expect(getRowsFromPosition(fileTemplateInformation, 2, 1)).toEqual([
    ['Sku', 'Name', 'Price', 'Enabled', 'Release date', 'Price with tax'],
    ['ref1', 'Produit 1', '$13.87 ', 'TRUE', '3/22/2022', '14.4'],
    ['ref2', 'Produit 2', '$12.00 ', 'FALSE', '5/23/2022', '16.644'],
  ]);

  expect(getRowsFromPosition(fileTemplateInformation, 3, 2)).toEqual([
    ['Produit 1', '$13.87 ', 'TRUE', '3/22/2022', '14.4'],
    ['Produit 2', '$12.00 ', 'FALSE', '5/23/2022', '16.644'],
  ]);

  expect(getRowsFromPosition(fileTemplateInformation, 5)).toEqual([]);
});

test('it returns empty rows when position is invalid', () => {
  expect(getRowsFromPosition(fileTemplateInformation, -1)).toEqual([]);
  expect(getRowsFromPosition(fileTemplateInformation, -1, -1)).toEqual([]);
  expect(getRowsFromPosition(fileTemplateInformation, 2, -1)).toEqual([]);
});
