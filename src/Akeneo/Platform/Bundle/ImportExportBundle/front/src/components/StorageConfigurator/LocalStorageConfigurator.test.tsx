import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {LocalStorage, SftpStorage} from '../../models';

test('it renders the local storage configurator', () => {
  const storage: LocalStorage = {
    type: 'local',
    file_path: '/tmp/file.xlsx',
  };

  renderWithProviders(
    <LocalStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByDisplayValue('/tmp/file.xlsx')).toBeInTheDocument();
});

test('it allows user to fill local storage file_path field', () => {
  const storage: LocalStorage = {
    type: 'local',
    file_path: '/tmp/file.xls',
  };
  const onStorageChange = jest.fn();

  renderWithProviders(
    <LocalStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const file_pathInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.file_path.label pim_common.required_label'
  );
  userEvent.type(file_pathInput, 'x');

  expect(onStorageChange).toHaveBeenLastCalledWith({
    type: 'local',
    file_path: '/tmp/file.xlsx',
  });
});

test('it throws an exception when passing a non-local storage', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '/tmp/file.xlsx',
    host: 'localhost',
    port: 22,
    login_type: 'password',
    username: 'root',
    password: 'root',
  };

  expect(() =>
    renderWithProviders(
      <LocalStorageConfigurator
        jobInstanceCode="csv_product_export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={jest.fn()}
      />
    )
  ).toThrowError('Invalid storage type "sftp" for local storage configurator');

  mockedConsole.mockRestore();
});

test('it displays validation errors', () => {
  const storage: LocalStorage = {
    type: 'local',
    file_path: '/tmp/file.xlsx',
  };

  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.a_file_path_error',
      invalidValue: '',
      message: 'this is a file_path error',
      parameters: {},
      propertyPath: '[file_path]',
    },
  ];

  renderWithProviders(
    <LocalStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={validationErrors}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.a_file_path_error')).toBeInTheDocument();
});
