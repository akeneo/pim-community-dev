import React from 'react';
import {ValidationError} from '@akeneo-pim-community/shared';
import {screen, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {FileTemplateConfigurator} from './FileTemplateConfigurator';
import {renderWithProviders} from 'feature/tests';
import {FileStructure} from '../../models';

const fileStructure: FileStructure = {
  header_row: 1,
  first_column: 1,
  first_product_row: 2,
  unique_identifier_column: 1,
  sheet_name: 'currentTestSheet',
};

test('it display correct value when provided with a templateInformation', async () => {
  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={{
        sheet_names: ['currentTestSheet', 'anotherTestSheet'],
        rows: [['sku', 'name', 'description']],
        column_count: 3,
      }}
      fileStructure={fileStructure}
      onFileStructureChange={jest.fn()}
      onSheetChange={jest.fn()}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.header_row')).toBeInTheDocument();
  expect(screen.getByLabelText('akeneo.tailored_import.file_structure.modal.header_row')).toHaveValue(1);
  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.first_product_row')).toBeInTheDocument();
  expect(screen.getByLabelText('akeneo.tailored_import.file_structure.modal.first_product_row')).toHaveValue(2);
  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.first_column')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.unique_identifier_column')).toBeInTheDocument();
});

test('it dispatch an event when sheet is changed', async () => {
  const handleSheetChange = jest.fn();

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={{
        sheet_names: ['currentTestSheet', 'anotherTestSheet'],
        rows: [['sku', 'name', 'description']],
        column_count: 3,
      }}
      fileStructure={fileStructure}
      onFileStructureChange={jest.fn()}
      onSheetChange={handleSheetChange}
      validationErrors={[]}
    />
  );

  await userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.sheet'));
  await userEvent.click(screen.getByText('anotherTestSheet'));

  expect(handleSheetChange).toBeCalledWith('anotherTestSheet');
});

test('it dispatch an event when header row change', async () => {
  const handleFileStructureChange = jest.fn();

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={{
        sheet_names: ['currentTestSheet', 'anotherTestSheet'],
        rows: [['sku', 'name', 'description']],
        column_count: 3,
      }}
      fileStructure={fileStructure}
      onFileStructureChange={handleFileStructureChange}
      onSheetChange={jest.fn()}
      validationErrors={[]}
    />
  );

  const input = screen.getByLabelText('akeneo.tailored_import.file_structure.modal.header_row');

  fireEvent.change(input, {target: {value: '2'}});
  expect(handleFileStructureChange).toBeCalledWith({...fileStructure, header_row: 2});
});

test('it dispatch event when first product row change', async () => {
  const handleFileStructureChange = jest.fn();

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={{
        sheet_names: ['currentTestSheet', 'anotherTestSheet'],
        rows: [['sku', 'name', 'description']],
        column_count: 3,
      }}
      fileStructure={fileStructure}
      onFileStructureChange={handleFileStructureChange}
      onSheetChange={jest.fn()}
      validationErrors={[]}
    />
  );

  const input = screen.getByLabelText('akeneo.tailored_import.file_structure.modal.first_product_row');

  fireEvent.change(input, {target: {value: '4'}});
  expect(handleFileStructureChange).toBeCalledWith({...fileStructure, first_product_row: 4});
});

test('it dispatch an event when first column row change', async () => {
  const handleFileStructureChange = jest.fn();

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={{
        sheet_names: ['currentTestSheet', 'anotherTestSheet'],
        rows: [['sku', 'name', 'description']],
        column_count: 3,
      }}
      fileStructure={fileStructure}
      onFileStructureChange={handleFileStructureChange}
      onSheetChange={jest.fn()}
      validationErrors={[]}
    />
  );

  await userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.first_column'));
  await userEvent.click(screen.getByText('sku (A)'));

  expect(handleFileStructureChange).toBeCalledWith({...fileStructure, first_column: 0});
});

test('it dispatch an event when column identifier change', async () => {
  const handleFileStructureChange = jest.fn();

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={{
        sheet_names: ['currentTestSheet', 'anotherTestSheet'],
        rows: [['sku', 'name', 'description']],
        column_count: 3,
      }}
      fileStructure={fileStructure}
      onFileStructureChange={handleFileStructureChange}
      onSheetChange={jest.fn()}
      validationErrors={[]}
    />
  );

  await userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.unique_identifier_column'));
  await userEvent.click(screen.getByText('sku (A)'));

  expect(handleFileStructureChange).toBeCalledWith({...fileStructure, unique_identifier_column: 0});
});

test('it display validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'sheet_error.key.name',
      invalidValue: '',
      message: 'this is a sheet validation error',
      parameters: {},
      propertyPath: '[sheet_name]',
    },
    {
      messageTemplate: 'header_row_error.key.name',
      invalidValue: '',
      message: 'this is a header row validation error',
      parameters: {},
      propertyPath: '[header_row]',
    },
    {
      messageTemplate: 'first_product_row_error.key.name',
      invalidValue: '',
      message: 'this is a first product row validation error',
      parameters: {},
      propertyPath: '[first_product_row]',
    },
    {
      messageTemplate: 'first_column_error.key.name',
      invalidValue: '',
      message: 'this is a first column validation error',
      parameters: {},
      propertyPath: '[first_column]',
    },
    {
      messageTemplate: 'unique_identifier_column_error.key.name',
      invalidValue: '',
      message: 'this is a unique identifier column validation error',
      parameters: {},
      propertyPath: '[unique_identifier_column]',
    },
  ];

  await renderWithProviders(
    <FileTemplateConfigurator
      fileTemplateInformation={{
        sheet_names: ['currentTestSheet', 'anotherTestSheet'],
        rows: [['sku', 'name', 'description']],
        column_count: 3,
      }}
      fileStructure={fileStructure}
      onFileStructureChange={jest.fn()}
      onSheetChange={jest.fn()}
      validationErrors={validationErrors}
    />
  );

  expect(screen.getByText('first_column_error.key.name')).toBeInTheDocument();
  expect(screen.getByText('header_row_error.key.name')).toBeInTheDocument();
  expect(screen.getByText('first_product_row_error.key.name')).toBeInTheDocument();
  expect(screen.getByText('first_column_error.key.name')).toBeInTheDocument();
  expect(screen.getByText('unique_identifier_column_error.key.name')).toBeInTheDocument();
});
