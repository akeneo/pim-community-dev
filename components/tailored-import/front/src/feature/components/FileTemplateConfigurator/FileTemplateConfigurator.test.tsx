import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {FileTemplateConfigurator} from './FileTemplateConfigurator';
import {renderWithProviders} from 'feature/tests';
import {FileStructure, FileTemplateInformation} from '../../models';

const fileInfo = {
  originalFilename: 'foo.xlsx',
  filePath: 'path/to/foo.xlsx',
};

const fileStructure = {
  header_line: 1,
  first_column: 1,
  product_line: 2,
  column_identifier_position: 1,
  sheet_name: 'currentTestSheet',
} as FileStructure;

test('it display correct value when provided with a templateInformation', async () => {
  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={
        {
          file_info: fileInfo,
          current_sheet: 'currentTestSheet',
          sheet_names: ['currentTestSheet', 'anotherTestSheet'],
          header_cells: ['sku', 'name', 'description'],
        } as FileTemplateInformation
      }
      fileStructure={fileStructure}
      onFileStructureChange={jest.fn()}
      onHeaderPositionChange={jest.fn()}
      onSheetChange={jest.fn()}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.header_position')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.product_position')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.column_position')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_import.file_structure.modal.column_identifier_position')
  ).toBeInTheDocument();
});

test('it dispatch an event when sheet is changed', async () => {
  const handleSheetChange = jest.fn();

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={
        {
          file_info: fileInfo,
          current_sheet: 'currentTestSheet',
          sheet_names: ['currentTestSheet', 'anotherTestSheet'],
          header_cells: ['sku', 'name', 'description'],
        } as FileTemplateInformation
      }
      fileStructure={fileStructure}
      onFileStructureChange={jest.fn()}
      onHeaderPositionChange={jest.fn()}
      onSheetChange={handleSheetChange}
    />
  );

  await userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.sheet'));
  await userEvent.click(screen.getByText('anotherTestSheet'));

  expect(handleSheetChange).toBeCalledWith('anotherTestSheet');
});

test('it dispatch an event when header position change', async () => {
  const handleHeaderPositionChange = jest.fn();

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={
        {
          file_info: fileInfo,
          current_sheet: 'currentTestSheet',
          sheet_names: ['currentTestSheet', 'anotherTestSheet'],
          header_cells: ['sku', 'name', 'description'],
        } as FileTemplateInformation
      }
      fileStructure={fileStructure}
      onFileStructureChange={jest.fn()}
      onHeaderPositionChange={handleHeaderPositionChange}
      onSheetChange={jest.fn()}
    />
  );

  const input = screen.getByLabelText('akeneo.tailored_import.file_structure.modal.header_position');

  fireEvent.change(input, {target: {value: '2'}});
  expect(handleHeaderPositionChange).toBeCalledWith(2);
});

test('it dispatch event when product position change', async () => {
  const handleFileStructureChange = jest.fn();

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={
        {
          file_info: fileInfo,
          current_sheet: 'currentTestSheet',
          sheet_names: ['currentTestSheet', 'anotherTestSheet'],
          header_cells: ['sku', 'name', 'description'],
        } as FileTemplateInformation
      }
      fileStructure={fileStructure}
      onFileStructureChange={handleFileStructureChange}
      onHeaderPositionChange={jest.fn()}
      onSheetChange={jest.fn()}
    />
  );

  const input = screen.getByLabelText('akeneo.tailored_import.file_structure.modal.product_position');

  fireEvent.change(input, {target: {value: '4'}});
  expect(handleFileStructureChange).toBeCalledWith({...fileStructure, product_line: 4});
});

test('it dispatch an event when column position change', async () => {
  const handleFileStructureChange = jest.fn();

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={
        {
          file_info: fileInfo,
          current_sheet: 'currentTestSheet',
          sheet_names: ['currentTestSheet', 'anotherTestSheet'],
          header_cells: ['sku', 'name', 'description'],
        } as FileTemplateInformation
      }
      fileStructure={fileStructure}
      onFileStructureChange={handleFileStructureChange}
      onHeaderPositionChange={jest.fn()}
      onSheetChange={jest.fn()}
    />
  );

  await userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.column_position'));
  await userEvent.click(screen.getByText('sku (A)'));

  expect(handleFileStructureChange).toBeCalledWith({...fileStructure, first_column: 0});
});

test('it dispatch an event when column identifier change', async () => {
  const handleFileStructureChange = jest.fn();

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={
        {
          file_info: fileInfo,
          current_sheet: 'currentTestSheet',
          sheet_names: ['currentTestSheet', 'anotherTestSheet'],
          header_cells: ['sku', 'name', 'description'],
        } as FileTemplateInformation
      }
      fileStructure={fileStructure}
      onFileStructureChange={handleFileStructureChange}
      onHeaderPositionChange={jest.fn()}
      onSheetChange={jest.fn()}
    />
  );

  await userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.column_identifier_position'));
  await userEvent.click(screen.getByText('sku (A)'));

  expect(handleFileStructureChange).toBeCalledWith({...fileStructure, column_identifier_position: 0});
});
