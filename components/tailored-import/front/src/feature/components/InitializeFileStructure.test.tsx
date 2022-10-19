import React from 'react';
import {FileInfo} from 'akeneo-design-system';
import {ValidationError} from '@akeneo-pim-community/shared';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '../tests';
import {useReadColumns} from '../hooks';
import {Column, FileStructure} from '../models';
import {InitializeFileStructure} from './InitializeFileStructure';

const mockedUseReadColumns = useReadColumns as jest.Mock;

const mockedColumns: Column[] = [
  {
    label: 'sku',
    uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
    index: 0,
  },
];

jest.mock('./FileTemplateConfigurator/FileTemplateUploader', () => ({
  FileTemplateUploader: ({onFileTemplateUpload}: {onFileTemplateUpload: (fileInfo: FileInfo) => void}) => (
    <button onClick={() => onFileTemplateUpload({filePath: 'path/to/foo.xlsx', originalFilename: 'foo.xlsx'})}>
      Upload file
    </button>
  ),
}));

jest.mock('./FileTemplateConfigurator/FileTemplateConfiguration', () => ({
  FileTemplateConfiguration: ({
    fileStructure,
    onFileStructureChange,
    validationErrors,
  }: {
    fileStructure: FileStructure;
    onFileStructureChange: (fileStructure: FileStructure) => void;
    validationErrors: ValidationError[];
  }) => (
    <>
      <>{fileStructure.sheet_name ?? 'first sheet'}</>
      <button
        onClick={() =>
          onFileStructureChange({
            sheet_name: 'second sheet',
            header_row: 1,
            first_product_row: 2,
            first_column: 0,
            unique_identifier_column: 0,
          })
        }
      >
        Change file structure
      </button>
      {validationErrors.map((error, index) => (
        <div key={index}>{error.messageTemplate}</div>
      ))}
    </>
  ),
}));

jest.mock('../hooks/useReadColumns', () => ({
  useReadColumns: jest.fn(),
}));

beforeEach(() => {
  mockedUseReadColumns.mockImplementation(() => (): Column[] => mockedColumns);
});

test('it displays a placeholder and a button when the file is not yet uploaded', async () => {
  await renderWithProviders(<InitializeFileStructure initialFileKey={null} onConfirm={jest.fn()} />);

  expect(screen.getByText('akeneo.tailored_import.file_structure.placeholder.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button')).toBeInTheDocument();
});

test('it can upload and send back a list of columns', async () => {
  const handleConfirm = jest.fn();

  await renderWithProviders(<InitializeFileStructure initialFileKey={null} onConfirm={handleConfirm} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));
  userEvent.click(screen.getByText('Upload file'));
  userEvent.click(screen.getByText('Change file structure'));

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).toHaveBeenCalledWith('path/to/foo.xlsx', mockedColumns, mockedColumns[0], {
    header_row: 1,
    first_column: 0,
    first_product_row: 2,
    sheet_name: 'second sheet',
    unique_identifier_column: 0,
  });
});

test('it displays validation errors when read columns fails', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  mockedUseReadColumns.mockImplementation(
    () => () =>
      Promise.reject([
        {
          messageTemplate: 'error.key.a_read_columns_error',
          invalidValue: '',
          message: 'this is a read columns error',
          parameters: {},
          propertyPath: '[file_structure][sheet_name]',
        },
      ])
  );

  await renderWithProviders(<InitializeFileStructure initialFileKey={null} onConfirm={jest.fn()} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));
  userEvent.click(screen.getByText('Upload file'));
  userEvent.click(screen.getByText('Change file structure'));
  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(screen.getByText('error.key.a_read_columns_error')).toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it displays global validation errors', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  mockedUseReadColumns.mockImplementation(
    () => () =>
      Promise.reject([
        {
          messageTemplate: 'error.key.a_global_error',
          invalidValue: '',
          message: 'this is a global error',
          parameters: {},
          propertyPath: '',
        },
      ])
  );

  await renderWithProviders(<InitializeFileStructure initialFileKey={null} onConfirm={jest.fn()} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));
  userEvent.click(screen.getByText('Upload file'));
  userEvent.click(screen.getByText('Change file structure'));
  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(screen.getByText('error.key.a_global_error')).toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it clears the uploaded file when the user closes the modal', async () => {
  const handleConfirm = jest.fn();

  await renderWithProviders(<InitializeFileStructure initialFileKey={null} onConfirm={handleConfirm} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));
  userEvent.click(screen.getByText('Upload file'));
  userEvent.click(screen.getByTitle('pim_common.close'));

  expect(screen.queryByText('akeneo.tailored_import.file_structure.modal.title')).not.toBeInTheDocument();
});

test('it clears the file structure information when user click on previous', async () => {
  await renderWithProviders(<InitializeFileStructure initialFileKey={null} onConfirm={jest.fn()} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));

  userEvent.click(screen.getByText('Upload file'));
  expect(screen.getByText('first sheet')).toBeInTheDocument();
  userEvent.click(screen.getByText('Change file structure'));
  expect(screen.getByText('second sheet')).toBeInTheDocument();
  userEvent.click(screen.getByText('pim_common.previous'));
  userEvent.click(screen.getByText('Upload file'));

  expect(screen.getByText('first sheet')).toBeInTheDocument();
});

test('it does not display previous button when initial file key is given', async () => {
  await renderWithProviders(<InitializeFileStructure initialFileKey={'path/to/file.xlsx'} onConfirm={jest.fn()} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));

  expect(screen.queryByText('Upload file')).not.toBeInTheDocument();

  expect(screen.getByText('first sheet')).toBeInTheDocument();
  userEvent.click(screen.getByText('Change file structure'));
  expect(screen.getByText('second sheet')).toBeInTheDocument();

  expect(screen.queryByText('pim_common.previous')).not.toBeInTheDocument();
});
