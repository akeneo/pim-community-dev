import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {useUploader} from '@akeneo-pim-community/shared';
import {renderWithProviders} from 'feature/tests';
import {useReadColumns, useFileTemplateInformationFetcher} from 'feature/hooks';
import {Column, FileTemplateInformation} from 'feature/models';
import {InitializeFileStructure} from './InitializeFileStructure';

const mockedUseUploader = useUploader as jest.Mock;
const mockedUseReadColumns = useReadColumns as jest.Mock;
const mockedUseFileTemplateInformationFetcher = useFileTemplateInformationFetcher as jest.Mock;

const mockedColumns: Column[] = [
  {
    label: 'sku',
    uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
    index: 0,
  },
];

jest.mock('@akeneo-pim-community/shared/lib/hooks/useUploader', () => ({
  useUploader: jest.fn(),
}));

jest.mock('../hooks/useReadColumns', () => ({
  useReadColumns: jest.fn(),
}));

jest.mock('../hooks/useFileTemplateInformationFetcher', () => ({
  useFileTemplateInformationFetcher: jest.fn(),
}));

let mockFetchFileTemplate = jest.fn();

beforeEach(() => {
  mockedUseUploader.mockImplementation(() => [
    jest.fn().mockResolvedValue({
      originalFilename: 'foo.xlsx',
      filePath: 'path/to/foo.xlsx',
    }),
  ]);
  mockedUseReadColumns.mockImplementation(() => (): Column[] => mockedColumns);
  mockFetchFileTemplate = jest.fn((): Promise<FileTemplateInformation> => {
    return Promise.resolve({
      sheet_names: ['currentTestSheet', 'anotherTestSheet'],
      rows: [],
      cell_number: 0,
    });
  });
  mockedUseFileTemplateInformationFetcher.mockImplementation(() => mockFetchFileTemplate);
});

test('it displays a placeholder and a button when the file is not yet uploaded', async () => {
  await renderWithProviders(<InitializeFileStructure onConfirm={jest.fn()} />);

  expect(screen.getByText('akeneo.tailored_import.file_structure.placeholder.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button')).toBeInTheDocument();
  expect(
    screen.queryByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder')
  ).not.toBeInTheDocument();
});

test('it can upload and send back a list of columns', async () => {
  const handleConfirm = jest.fn();

  await renderWithProviders(<InitializeFileStructure onConfirm={handleConfirm} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));

  await act(async () => {
    userEvent.upload(
      screen.getByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder'),
      new File(['foo'], 'foo.xlsx', {type: 'application/vnd.ms-excel'})
    );
  });

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).toHaveBeenCalledWith('path/to/foo.xlsx', mockedColumns, mockedColumns[0], {
    header_row: 1,
    first_column: 0,
    first_product_row: 2,
    sheet_name: 'currentTestSheet',
    unique_identifier_column: 0,
  });
});

test('it clears the uploaded file when the user closes the modal', async () => {
  const handleConfirm = jest.fn();

  await renderWithProviders(<InitializeFileStructure onConfirm={handleConfirm} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));

  await act(async () => {
    userEvent.upload(
      screen.getByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder'),
      new File(['foo'], 'foo.xlsx', {type: 'application/vnd.ms-excel'})
    );
  });

  await act(async () => {
    userEvent.click(screen.getByTitle('pim_common.close'));
  });

  expect(screen.queryByText('akeneo.tailored_import.file_structure.modal.title')).not.toBeInTheDocument();
});

test('it displays validation errors when upload fails', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  mockedUseUploader.mockImplementation(() => [
    () =>
      Promise.reject(
        JSON.stringify([
          {
            messageTemplate: 'error.key.an_upload_error',
            invalidValue: '',
            message: 'this is an upload error',
            parameters: {},
            propertyPath: '[file]',
          },
        ])
      ),
  ]);

  await renderWithProviders(<InitializeFileStructure onConfirm={jest.fn()} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));

  await act(async () => {
    userEvent.upload(
      screen.getByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder'),
      new File(['foo'], 'foo.xlsx', {type: 'application/vnd.ms-excel'})
    );
  });

  expect(screen.getByText('error.key.an_upload_error')).toBeInTheDocument();
  mockedConsole.mockRestore();
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

  await renderWithProviders(<InitializeFileStructure onConfirm={jest.fn()} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));

  await act(async () => {
    userEvent.upload(
      screen.getByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder'),
      new File(['foo'], 'foo.xlsx', {type: 'application/vnd.ms-excel'})
    );
  });

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(screen.getByText('error.key.a_read_columns_error')).toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it refresh file information when sheet changed', async () => {
  const handleConfirm = jest.fn();

  await renderWithProviders(<InitializeFileStructure onConfirm={handleConfirm} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));

  await act(async () => {
    userEvent.upload(
      screen.getByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder'),
      new File(['foo'], 'foo.xlsx', {type: 'application/vnd.ms-excel'})
    );
  });

  await userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.sheet'));
  await act(async () => {
    await userEvent.click(screen.getByText('anotherTestSheet'));
  });

  expect(mockFetchFileTemplate).toHaveBeenCalledWith(
    {
      originalFilename: 'foo.xlsx',
      filePath: 'path/to/foo.xlsx',
    },
    'anotherTestSheet'
  );
});

test('it clears the file structure information when user click on previous', async () => {
  await renderWithProviders(<InitializeFileStructure onConfirm={jest.fn()} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.placeholder.button'));

  await act(async () => {
    userEvent.upload(
      screen.getByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder'),
      new File(['foo'], 'foo.xlsx', {type: 'application/vnd.ms-excel'})
    );
  });

  await userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.sheet'));
  await act(async () => {
    await userEvent.click(screen.getByText('anotherTestSheet'));
  });

  userEvent.click(screen.getByText('pim_common.previous'));
  await act(async () => {
    userEvent.upload(
      screen.getByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder'),
      new File(['foo'], 'foo.xlsx', {type: 'application/vnd.ms-excel'})
    );
  });

  expect(screen.getByText('currentTestSheet')).toBeInTheDocument();
});
