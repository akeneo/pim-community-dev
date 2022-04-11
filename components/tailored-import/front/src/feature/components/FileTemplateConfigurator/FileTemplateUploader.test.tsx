import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {useUploader} from '@akeneo-pim-community/shared';
import {renderWithProviders} from 'feature/tests';
import {FileTemplateUploader} from './FileTemplateUploader';

const mockedUseUploader = useUploader as jest.Mock;

jest.mock('@akeneo-pim-community/shared/lib/hooks/useUploader', () => ({
  useUploader: jest.fn(),
}));

beforeEach(() => {
  mockedUseUploader.mockImplementation(() => [
    jest.fn().mockResolvedValue({
      originalFilename: 'foo.xlsx',
      filePath: 'path/to/foo.xlsx',
    }),
  ]);
});

test('it displays a placeholder and a button when the file is not yet uploaded', async () => {
  await renderWithProviders(<FileTemplateUploader onFileTemplateUpload={jest.fn()} />);

  expect(
    screen.queryByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder')
  ).toBeInTheDocument();
});

test('it calls handler when file is uploaded', async () => {
  const handleFileTemplateUpload = jest.fn();
  await renderWithProviders(<FileTemplateUploader onFileTemplateUpload={handleFileTemplateUpload} />);
  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.upload.placeholder'));

  await act(async () => {
    userEvent.upload(
      screen.getByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder'),
      new File(['foo'], 'foo.xlsx', {type: 'application/vnd.ms-excel'})
    );
  });

  expect(screen.getByText('akeneo.tailored_import.file_structure.modal.upload.placeholder')).toBeInTheDocument();
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

  await renderWithProviders(<FileTemplateUploader onFileTemplateUpload={jest.fn()} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.file_structure.modal.upload.placeholder'));

  await act(async () => {
    userEvent.upload(
      screen.getByPlaceholderText('akeneo.tailored_import.file_structure.modal.upload.placeholder'),
      new File(['foo'], 'foo.xlsx', {type: 'application/vnd.ms-excel'})
    );
  });

  expect(screen.getByText('error.key.an_upload_error')).toBeInTheDocument();
  mockedConsole.mockRestore();
});
