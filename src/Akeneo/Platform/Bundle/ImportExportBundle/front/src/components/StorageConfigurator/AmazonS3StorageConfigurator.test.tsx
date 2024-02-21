import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {AmazonS3StorageConfigurator} from './AmazonS3StorageConfigurator';
import {AmazonS3Storage, LocalStorage} from '../../models';

jest.mock('./CheckStorageConnection', () => ({
  CheckStorageConnection: () => <button>Check connection</button>,
}));

const storage: AmazonS3Storage = {
  type: 'amazon_s3',
  file_path: '/tmp/file.xlsx',
  region: 'eu-west-1',
  bucket: '',
  key: 'a_key',
  secret: 'my_s3cr3t',
};

test('it renders the amazon s3 storage configurator', () => {
  const storage: AmazonS3Storage = {
    type: 'amazon_s3',
    file_path: '/tmp/file.xlsx',
    region: 'eu-west-1',
    bucket: 'my_bucket',
    key: 'a_key',
    secret: 'my_s3cr3t',
  };

  renderWithProviders(
    <AmazonS3StorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByDisplayValue('/tmp/file.xlsx')).toBeInTheDocument();
  expect(screen.getByDisplayValue('eu-west-1')).toBeInTheDocument();
  expect(screen.getByDisplayValue('my_bucket')).toBeInTheDocument();
});

test('it allows user to fill file_path field', () => {
  const storage: AmazonS3Storage = {
    type: 'amazon_s3',
    file_path: '/tmp/test.xls',
    region: 'eu-west-1',
    bucket: 'my_bucket',
    key: 'a_key',
    secret: 'my_s3cr3t',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <AmazonS3StorageConfigurator
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

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, file_path: '/tmp/test.xlsx'});
});

test('it allows user to fill region field', () => {
  const storage: AmazonS3Storage = {
    type: 'amazon_s3',
    file_path: '/tmp/file.xlsx',
    region: 'eu-west-',
    bucket: 'my_bucket',
    key: 'a_key',
    secret: 'my_s3cr3t',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <AmazonS3StorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const regionInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.region.label pim_common.required_label'
  );
  userEvent.type(regionInput, '1');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, region: 'eu-west-1'});
});

test('it allows user to fill bucket field', () => {
  const onStorageChange = jest.fn();

  renderWithProviders(
    <AmazonS3StorageConfigurator
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

test('it allows user to fill key field', () => {
  const storage: AmazonS3Storage = {
    type: 'amazon_s3',
    file_path: '/tmp/file.xlsx',
    region: 'eu-west-1',
    bucket: '',
    key: 'a_ke',
    secret: 'my_s3cr3t',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <AmazonS3StorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const keyInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.key.label pim_common.required_label'
  );
  userEvent.type(keyInput, 'y');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, key: 'a_key'});
});

test('it allows user to fill secret field', () => {
  const storage: AmazonS3Storage = {
    type: 'amazon_s3',
    file_path: '/tmp/file.xlsx',
    region: 'eu-west-1',
    bucket: '',
    key: 'a_key',
    secret: 'my_s3cr3',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <AmazonS3StorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const secretInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.secret.label pim_common.required_label'
  );
  userEvent.type(secretInput, 't');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, secret: 'my_s3cr3t'});
});

test('it hides secret field if the secret is obfuscated', () => {
  const storage: AmazonS3Storage = {
    type: 'amazon_s3',
    file_path: '/tmp/file.xlsx',
    region: 'eu-west-1',
    bucket: '',
    key: 'a_key',
  };

  renderWithProviders(
    <AmazonS3StorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  const secretInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.secret.label pim_common.required_label'
  );

  expect(secretInput).toBeDisabled();
  expect(secretInput).toHaveValue('••••••••');
});

test('it can edit the secret field if the secret is obfuscated', () => {
  const storage: AmazonS3Storage = {
    type: 'amazon_s3',
    file_path: '/tmp/file.xlsx',
    region: 'eu-west-1',
    bucket: '',
    key: 'a_key',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <AmazonS3StorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, secret: ''});
});

test('it throws an exception when passing a non amazon s3 storage', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  const storage: LocalStorage = {
    type: 'local',
    file_path: '/tmp/file.xlsx',
  };

  expect(() =>
    renderWithProviders(
      <AmazonS3StorageConfigurator
        jobInstanceCode="csv_product_export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={jest.fn()}
      />
    )
  ).toThrowError('Invalid storage type "local" for amazon s3 storage configurator');

  mockedConsole.mockRestore();
});

test('it displays validation errors', () => {
  const storage: AmazonS3Storage = {
    type: 'amazon_s3',
    file_path: '/tmp/file.xlsx',
    region: 'invalid',
    bucket: 'foo',
    key: 'a_key',
    secret: 'my_s3cr3t',
  };

  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.a_file_path_error',
      invalidValue: '',
      message: 'this is a file_path error',
      parameters: {},
      propertyPath: '[file_path]',
    },
    {
      messageTemplate: 'error.key.a_region_error',
      invalidValue: '',
      message: 'this is a region error',
      parameters: {},
      propertyPath: '[region]',
    },
    {
      messageTemplate: 'error.key.a_bucket_error',
      invalidValue: '',
      message: 'this is a bucket error',
      parameters: {},
      propertyPath: '[bucket]',
    },
    {
      messageTemplate: 'error.key.a_key_error',
      invalidValue: '',
      message: 'this is a key error',
      parameters: {},
      propertyPath: '[key]',
    },
    {
      messageTemplate: 'error.key.a_secret_error',
      invalidValue: '',
      message: 'this is a secret error',
      parameters: {},
      propertyPath: '[secret]',
    },
  ];

  renderWithProviders(
    <AmazonS3StorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={validationErrors}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.a_file_path_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_region_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_bucket_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_key_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_secret_error')).toBeInTheDocument();
});

test('it can check connection', () => {
  renderWithProviders(
    <AmazonS3StorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByText('Check connection')).toBeInTheDocument();
});
