import React from 'react';
import {act, fireEvent, screen} from '@testing-library/react';
import {FileTemplateConfiguration} from './FileTemplateConfiguration';
import {renderWithProviders} from 'feature/tests';
import {FileStructure, FileTemplateInformation} from '../../models';

const fileStructure: FileStructure = {
  header_row: 1,
  first_column: 0,
  first_product_row: 2,
  unique_identifier_column: 0,
  sheet_name: 'first sheet',
};

let mockFileTemplateInformationFetcher: jest.Mock;
beforeEach(() => {
  mockFileTemplateInformationFetcher = jest.fn(
    (): Promise<FileTemplateInformation> =>
      Promise.resolve({
        sheet_names: ['first sheet', 'second sheet'],
        rows: [
          ['Sku', 'Name', 'Price', 'Enabled', 'Release date', 'Price with tax'],
          ['ref1', 'Produit 1', '$13.87', 'TRUE', '3/22/2022', '14.4'],
          ['ref2', 'Produit 2', '$12.00', 'FALSE', '5/23/2022', '16.644'],
        ],
        column_count: 6,
      })
  );
});

jest.mock('../../hooks/useFileTemplateInformationFetcher', () => ({
  useFileTemplateInformationFetcher: () => mockFileTemplateInformationFetcher,
}));

test('it displays inputs in order to modify the file structure and a preview of data', async () => {
  await renderWithProviders(
    <FileTemplateConfiguration
      fileStructure={fileStructure}
      onFileStructureChange={jest.fn()}
      validationErrors={[]}
      fileKey="path/to/foo.xlsx"
    />
  );

  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.header_row')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.first_product_row')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.first_column')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.unique_identifier_column')).toBeInTheDocument();

  expect(screen.getByText('Price with tax')).toBeInTheDocument();
  expect(screen.getByText('ref1')).toBeInTheDocument();
  expect(screen.getByText('14.4')).toBeInTheDocument();
  expect(screen.getByText('ref2')).toBeInTheDocument();
  expect(screen.getByText('16.644')).toBeInTheDocument();
});

test('it change file structure and refresh displayed preview when sheet is changed', async () => {
  const handleFileStructureChange = jest.fn(
    (dispatch: FileStructure | ((fileStructure: FileStructure) => FileStructure)): void => {
      if (typeof dispatch === 'function') {
        void dispatch(fileStructure);
      }
    }
  );

  await renderWithProviders(
    <FileTemplateConfiguration
      fileStructure={fileStructure}
      onFileStructureChange={handleFileStructureChange}
      validationErrors={[]}
      fileKey="path/to/foo.xlsx"
    />
  );

  fireEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.sheet'));
  await act(async () => {
    await fireEvent.click(screen.getByText('second sheet'));
  });

  expect(mockFileTemplateInformationFetcher).toHaveBeenCalledWith('path/to/foo.xlsx', 'second sheet');
  expect(handleFileStructureChange).toHaveBeenCalledWith({
    header_row: 1,
    first_column: 0,
    first_product_row: 2,
    unique_identifier_column: 0,
    sheet_name: 'second sheet',
  });
});
