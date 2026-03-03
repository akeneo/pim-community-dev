import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {GoogleCloudStorageConfigurator} from './GoogleCloudStorageConfigurator';
import {GoogleCloudStorage, LocalStorage} from '../../models';

jest.mock('./CheckStorageConnection', () => ({
  CheckStorageConnection: () => <button>Check connection</button>,
}));

const storage: GoogleCloudStorage = {
  type: 'google_cloud_storage',
  file_path: '/tmp/file.xlsx',
  project_id: 'a_project_id',
  service_account: '{"type": "service_account"}',
  bucket: 'a_bucket',
};

test('it renders the google cloud storage configurator', () => {
  renderWithProviders(
    <GoogleCloudStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByDisplayValue('/tmp/file.xlsx')).toBeInTheDocument();
  expect(screen.getByDisplayValue('a_project_id')).toBeInTheDocument();
  expect(screen.getByDisplayValue('{"type": "service_account"}')).toBeInTheDocument();
  expect(screen.getByDisplayValue('a_bucket')).toBeInTheDocument();
});

test('it allows user to fill file_path field', () => {
  const storage: GoogleCloudStorage = {
    type: 'google_cloud_storage',
    file_path: '/tmp/file.xls',
    project_id: 'a_project_id',
    service_account: '{"type": "service_account"}',
    bucket: 'a_bucket',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <GoogleCloudStorageConfigurator
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

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, file_path: '/tmp/file.xlsx'});
});

test('it allows user to fill project_id field', () => {
  const storage: GoogleCloudStorage = {
    type: 'google_cloud_storage',
    file_path: '/tmp/file.xlsx',
    project_id: 'a_project_i',
    service_account: '{"type": "service_account"}',
    bucket: 'a_bucket',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <GoogleCloudStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const projectIdInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.project_id.label pim_common.required_label'
  );
  userEvent.type(projectIdInput, 'd');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, project_id: 'a_project_id'});
});

test('it allows user to fill service_account field', () => {
  const storage: GoogleCloudStorage = {
    type: 'google_cloud_storage',
    file_path: '/tmp/file.xlsx',
    project_id: 'a_project_id',
    service_account: '{"type": "service_account"',
    bucket: 'a_bucket',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <GoogleCloudStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const serviceAccountInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.service_account.label pim_common.required_label'
  );
  userEvent.type(serviceAccountInput, '}');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, service_account: '{"type": "service_account"}'});
});

test('it allows user to fill bucket field', () => {
  const storage: GoogleCloudStorage = {
    type: 'google_cloud_storage',
    file_path: '/tmp/file.xlsx',
    project_id: 'a_project_id',
    service_account: '{"type": "service_account"}',
    bucket: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <GoogleCloudStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  userEvent.paste(
    screen.getByLabelText('pim_import_export.form.job_instance.storage_form.bucket.label pim_common.required_label'),
    'my_amazing_bucket'
  );

  expect(onStorageChange).toHaveBeenLastCalledWith({
    ...storage,
    bucket: 'my_amazing_bucket',
  });
});

test('it hides the service account field if the service account is obfuscated', () => {
  const storage: GoogleCloudStorage = {
    type: 'google_cloud_storage',
    file_path: '/tmp/file.xlsx',
    project_id: 'a_project_id',
    bucket: '',
  };

  renderWithProviders(
    <GoogleCloudStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  const serviceAccountInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.service_account.label pim_common.required_label'
  );

  expect(serviceAccountInput).toBeDisabled();
  expect(serviceAccountInput).toHaveValue('••••••••');
});

test('it can edit the service account if the service account is obfuscated', () => {
  const storage: GoogleCloudStorage = {
    type: 'google_cloud_storage',
    file_path: '/tmp/file.xlsx',
    project_id: 'a_project_id',
    bucket: '',
  };

  const onStorageChange = jest.fn();
  renderWithProviders(
    <GoogleCloudStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));
  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, service_account: ''});
});

test('it throws an exception when passing a non google cloud storage', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  const storage: LocalStorage = {
    type: 'local',
    file_path: '/tmp/file.xlsx',
  };

  expect(() =>
    renderWithProviders(
      <GoogleCloudStorageConfigurator
        jobInstanceCode="csv_product_export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={jest.fn()}
      />
    )
  ).toThrowError('Invalid storage type "local" for google cloud storage configurator');

  mockedConsole.mockRestore();
});

test('it displays validation errors', () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.a_file_path_error',
      invalidValue: '',
      message: 'this is a file_path error',
      parameters: {},
      propertyPath: '[file_path]',
    },
    {
      messageTemplate: 'error.key.a_project_id_error',
      invalidValue: '',
      message: 'this is a project id error',
      parameters: {},
      propertyPath: '[project_id]',
    },
    {
      messageTemplate: 'error.key.a_service_account_error',
      invalidValue: '',
      message: 'this is a service account error',
      parameters: {},
      propertyPath: '[service_account]',
    },
    {
      messageTemplate: 'error.key.a_bucket_error',
      invalidValue: '',
      message: 'this is a bucket error',
      parameters: {},
      propertyPath: '[bucket]',
    },
  ];

  renderWithProviders(
    <GoogleCloudStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={validationErrors}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.a_file_path_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_project_id_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_service_account_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_bucket_error')).toBeInTheDocument();
});

test('it can check connection', () => {
  renderWithProviders(
    <GoogleCloudStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByText('Check connection')).toBeInTheDocument();
});
