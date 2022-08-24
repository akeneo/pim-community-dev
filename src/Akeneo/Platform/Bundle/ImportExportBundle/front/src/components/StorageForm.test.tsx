import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError, useFeatureFlags} from '@akeneo-pim-community/shared';
import {StorageForm} from './StorageForm';
import {LocalStorage, NoneStorage, SftpStorage} from './model';

const mockedUseFeatureFlags = useFeatureFlags as jest.Mock;

jest.mock('@akeneo-pim-community/shared/lib/hooks/useFeatureFlags', () => ({
  useFeatureFlags: jest.fn(),
}));

beforeEach(() => {
  mockedUseFeatureFlags.mockImplementation(() => ({
    isEnabled: (featureFlag: string): boolean => true,
  }));
});

test('it renders the storage form', () => {
  const storage: NoneStorage = {
    type: 'none',
    file_path: '/tmp/file.xlsx',
  };

  renderWithProviders(
    <StorageForm
      jobType="export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByText('pim_import_export.form.job_instance.storage_form.connection.none')).toBeInTheDocument();
});

test('it triggers onStorageChange callback when storage configurator onStorageChange is triggered', () => {
  const storage: LocalStorage = {
    type: 'local',
    file_path: '/tmp/file.xls',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <StorageForm
      jobType="export"
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

test('it does not render the storage form configurator if storage is none', () => {
  const storage: NoneStorage = {
    type: 'none',
    file_path: '/tmp/file.xlsx',
  };

  renderWithProviders(
    <StorageForm
      jobType="export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  expect(
    screen.queryByText('pim_import_export.form.job_instance.storage_form.file_path.label')
  ).not.toBeInTheDocument();
});

test('it renders the storage form configurator if storage is local', () => {
  const storage: LocalStorage = {
    type: 'local',
    file_path: '',
  };

  renderWithProviders(
    <StorageForm
      jobType="export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  expect(
    screen.getByText('pim_import_export.form.job_instance.storage_form.file_path.label pim_common.required_label')
  ).toBeInTheDocument();
  expect(screen.queryByText('pim_import_export.form.job_instance.storage_form.host.label')).not.toBeInTheDocument();
});

test('it renders the storage form configurator if storage is sftp', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: '',
    port: 22,
    username: '',
    password: '',
  };

  renderWithProviders(
    <StorageForm
      jobType="export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={jest.fn()}
    />
  );

  expect(
    screen.getByText('pim_import_export.form.job_instance.storage_form.file_path.label pim_common.required_label')
  ).toBeInTheDocument();
  expect(
    screen.getByText('pim_import_export.form.job_instance.storage_form.host.label pim_common.required_label')
  ).toBeInTheDocument();
});

test('it can select a local storage', () => {
  const storage: NoneStorage = {
    type: 'none',
    file_path: '/tmp/file.xlsx',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <StorageForm
      jobType="export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('pim_import_export.form.job_instance.storage_form.connection.local'));

  expect(onStorageChange).toBeCalledWith({
    type: 'local',
    file_path: 'export_%job_label%_%datetime%.xlsx',
  });
});

test('it can select a sftp storage', () => {
  const storage: NoneStorage = {
    type: 'none',
    file_path: '/tmp/file.xlsx',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <StorageForm
      jobType="export"
      storage={storage}
      fileExtension="csv"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('pim_import_export.form.job_instance.storage_form.connection.sftp'));

  expect(onStorageChange).toBeCalledWith({
    type: 'sftp',
    file_path: 'export_%job_label%_%datetime%.csv',
    host: '',
    port: 22,
    username: '',
    password: '',
  });
});

test('it displays validation errors', () => {
  const storage: LocalStorage = {
    type: 'local',
    file_path: '',
  };

  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.a_type_error',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
    {
      messageTemplate: 'error.key.a_file_path_error',
      invalidValue: '',
      message: 'this is a file_path error passed to the configurator',
      parameters: {},
      propertyPath: '[file_path]',
    },
  ];

  renderWithProviders(
    <StorageForm
      jobType="export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={validationErrors}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.a_type_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_file_path_error')).toBeInTheDocument();
});
