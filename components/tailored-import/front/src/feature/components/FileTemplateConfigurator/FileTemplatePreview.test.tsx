import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from 'feature/tests';
import {FileStructure} from '../../models';
import {FileTemplatePreview} from './FileTemplatePreview';

const fileStructure: FileStructure = {
  header_row: 1,
  first_column: 0,
  first_product_row: 2,
  unique_identifier_column: 0,
  sheet_name: 'first sheet',
};

test('it display a placeholder when there is no rows', async () => {
  await renderWithProviders(
    <FileTemplatePreview
      fileTemplateInformation={{
        sheet_names: ['firstSheet', 'secondSheet'],
        rows: [],
        column_count: 3,
      }}
      fileStructure={fileStructure}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.validation.file_preview.empty_sheet.title')).toBeInTheDocument();
});

test('it display rows', async () => {
  await renderWithProviders(
    <FileTemplatePreview
      fileTemplateInformation={{
        sheet_names: ['first sheet', 'second sheet', 'third sheet'],
        rows: [
          ['Sku', 'Name', 'Price', 'Enabled', 'Release date', 'Price with tax'],
          ['ref1', 'Produit 1', '$13.87', 'TRUE', '3/22/2022', '14.4'],
          ['ref2', 'Produit 2', '$12.00', 'FALSE', '5/23/2022', '16.644'],
        ],
        column_count: 6,
      }}
      fileStructure={fileStructure}
    />
  );

  expect(screen.getByText('Sku')).toBeInTheDocument();
  expect(screen.getByText('Name')).toBeInTheDocument();
  expect(screen.getByText('Price')).toBeInTheDocument();
  expect(screen.getByText('Enabled')).toBeInTheDocument();
  expect(screen.getByText('Release date')).toBeInTheDocument();
  expect(screen.getByText('Price with tax')).toBeInTheDocument();

  expect(screen.getByText('ref1')).toBeInTheDocument();
  expect(screen.getByText('Produit 1')).toBeInTheDocument();
  expect(screen.getByText('$13.87')).toBeInTheDocument();
  expect(screen.getByText('TRUE')).toBeInTheDocument();
  expect(screen.getByText('3/22/2022')).toBeInTheDocument();
  expect(screen.getByText('14.4')).toBeInTheDocument();

  expect(screen.getByText('ref2')).toBeInTheDocument();
  expect(screen.getByText('Produit 2')).toBeInTheDocument();
  expect(screen.getByText('$12.00')).toBeInTheDocument();
  expect(screen.getByText('FALSE')).toBeInTheDocument();
  expect(screen.getByText('5/23/2022')).toBeInTheDocument();
  expect(screen.getByText('16.644')).toBeInTheDocument();
});

test('it did not display cell not visible according to file structure', async () => {
  const fileStructure: FileStructure = {
    header_row: 2,
    first_column: 1,
    first_product_row: 3,
    unique_identifier_column: 1,
    sheet_name: 'first sheet',
  };

  await renderWithProviders(
    <FileTemplatePreview
      fileTemplateInformation={{
        sheet_names: ['first sheet', 'second sheet', 'third sheet'],
        rows: [
          ['Sku', 'Name', 'Price', 'Enabled', 'Release date', 'Price with tax'],
          ['ref1', 'Produit 1', '$13.87', 'TRUE', '3/22/2022', '14.4'],
          ['ref2', 'Produit 2', '$12.00', 'FALSE', '5/23/2022', '16.644'],
        ],
        column_count: 7,
      }}
      fileStructure={fileStructure}
    />
  );

  expect(screen.queryByText('Sku')).not.toBeInTheDocument();
  expect(screen.queryByText('Name')).not.toBeInTheDocument();
  expect(screen.queryByText('Price')).not.toBeInTheDocument();
  expect(screen.queryByText('Enabled')).not.toBeInTheDocument();
  expect(screen.queryByText('Release date')).not.toBeInTheDocument();
  expect(screen.queryByText('Price with tax')).not.toBeInTheDocument();

  expect(screen.queryByText('ref1')).not.toBeInTheDocument();
  expect(screen.getByText('Produit 1')).toBeInTheDocument();
  expect(screen.getByText('$13.87')).toBeInTheDocument();
  expect(screen.getByText('TRUE')).toBeInTheDocument();
  expect(screen.getByText('3/22/2022')).toBeInTheDocument();
  expect(screen.getByText('14.4')).toBeInTheDocument();

  expect(screen.queryByText('ref2')).not.toBeInTheDocument();
  expect(screen.getByText('Produit 2')).toBeInTheDocument();
  expect(screen.getByText('$12.00')).toBeInTheDocument();
  expect(screen.getByText('FALSE')).toBeInTheDocument();
  expect(screen.getByText('5/23/2022')).toBeInTheDocument();
  expect(screen.getByText('16.644')).toBeInTheDocument();
});
