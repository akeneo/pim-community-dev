import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen, act} from '@testing-library/react';
import {renderWithProviders, ValidationError, useFeatureFlags} from '@akeneo-pim-community/shared';
import {StorageForm} from './StorageForm';
import {NoneStorage, LocalStorage, SftpStorage} from '../models';

const mockedUseFeatureFlags = useFeatureFlags as jest.Mock;

jest.mock('@akeneo-pim-community/shared/lib/hooks/useFeatureFlags', () => ({
  useFeatureFlags: jest.fn(),
}));

beforeEach(() => {
  mockedUseFeatureFlags.mockImplementation(() => ({
    isEnabled: (featureFlag: string): boolean => true,
  }));

  global.fetch = mockFetch;
});

const mockFetch = jest.fn().mockImplementation(async (route: string) => {
  switch (route) {
    case 'pimee_job_automation_get_public_key':
      return {
        ok: true,
        json: async () => '-----BEGIN CERTIFICATE-----publickey-----END CERTIFICATE-----',
      };
    case 'pimee_job_automation_get_storage_connection_check':
      return {
        ok: true,
      };
    default:
      throw new Error();
  }
});

test('it renders the storage form', async () => {
  const storage: NoneStorage = {
    type: 'none',
    file_path: '/tmp/file.xlsx',
  };

  await act(async () => {
    renderWithProviders(
      <StorageForm
        jobInstanceCode="csv_product_export"
        jobType="export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={jest.fn()}
      />
    );
  });

  expect(screen.getByText('pim_import_export.form.job_instance.storage_form.connection.none')).toBeInTheDocument();
});

test('it triggers onStorageChange callback when storage configurator onStorageChange is triggered', async () => {
  const storage: LocalStorage = {
    type: 'local',
    file_path: '/tmp/file.xls',
  };

  const onStorageChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <StorageForm
        jobInstanceCode="csv_product_export"
        jobType="export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={onStorageChange}
      />
    );
  });

  const file_pathInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.file_path.label pim_common.required_label'
  );
  userEvent.type(file_pathInput, 'x');

  expect(onStorageChange).toHaveBeenLastCalledWith({
    type: 'local',
    file_path: '/tmp/file.xlsx',
  });
});

test('it does not render the storage form configurator if storage is none', async () => {
  const storage: NoneStorage = {
    type: 'none',
    file_path: '/tmp/file.xlsx',
  };

  await act(async () => {
    renderWithProviders(
      <StorageForm
        jobInstanceCode="csv_product_export"
        jobType="export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={jest.fn()}
      />
    );
  });

  expect(
    screen.queryByText('pim_import_export.form.job_instance.storage_form.file_path.label')
  ).not.toBeInTheDocument();
});

test('it renders the storage form configurator if storage is local', async () => {
  const storage: LocalStorage = {
    type: 'local',
    file_path: '',
  };

  await act(async () => {
    renderWithProviders(
      <StorageForm
        jobInstanceCode="csv_product_export"
        jobType="export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={jest.fn()}
      />
    );
  });

  expect(
    screen.getByText('pim_import_export.form.job_instance.storage_form.file_path.label pim_common.required_label')
  ).toBeInTheDocument();
  expect(screen.queryByText('pim_import_export.form.job_instance.storage_form.host.label')).not.toBeInTheDocument();
});

test('it renders the storage form configurator if storage is sftp', async () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: '',
    port: 22,
    login_type: 'password',
    username: '',
    password: '',
  };

  await act(async () => {
    renderWithProviders(
      <StorageForm
        jobInstanceCode="csv_product_export"
        jobType="export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={jest.fn()}
      />
    );
  });

  expect(
    screen.getByText('pim_import_export.form.job_instance.storage_form.file_path.label pim_common.required_label')
  ).toBeInTheDocument();
  expect(
    screen.getByText('pim_import_export.form.job_instance.storage_form.host.label pim_common.required_label')
  ).toBeInTheDocument();
});

test('it can select a local storage', async () => {
  const storage: NoneStorage = {
    type: 'none',
    file_path: '/tmp/file.xlsx',
  };

  const onStorageChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <StorageForm
        jobInstanceCode="csv_product_export"
        jobType="export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={onStorageChange}
      />
    );
  });

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('pim_import_export.form.job_instance.storage_form.connection.local'));

  expect(onStorageChange).toBeCalledWith({
    type: 'local',
    file_path: '/tmp/export_%job_label%_%datetime%.xlsx',
  });
});

test('it can select a sftp storage', async () => {
  const storage: NoneStorage = {
    type: 'none',
    file_path: '/tmp/file.xlsx',
  };

  const onStorageChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <StorageForm
        jobInstanceCode="csv_product_export"
        jobType="export"
        storage={storage}
        fileExtension="csv"
        validationErrors={[]}
        onStorageChange={onStorageChange}
      />
    );
  });

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('pim_import_export.form.job_instance.storage_form.connection.sftp'));

  expect(onStorageChange).toBeCalledWith({
    type: 'sftp',
    file_path: 'export_%job_label%_%datetime%.csv',
    host: '',
    port: 22,
    login_type: 'password',
    username: '',
    password: '',
  });
});

test('it displays validation errors', async () => {
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

  await act(async () => {
    renderWithProviders(
      <StorageForm
        jobInstanceCode="csv_product_export"
        jobType="export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={validationErrors}
        onStorageChange={jest.fn()}
      />
    );
  });

  expect(screen.getByText('error.key.a_type_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_file_path_error')).toBeInTheDocument();
});
