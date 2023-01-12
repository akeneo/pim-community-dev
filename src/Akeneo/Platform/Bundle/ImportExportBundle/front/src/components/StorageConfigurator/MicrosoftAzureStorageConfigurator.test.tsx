import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen, act} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {LocalStorage, MicrosoftAzureStorage} from '../model';
import {MicrosoftAzureStorageConfigurator} from './MicrosoftAzureStorageConfigurator';

beforeEach(() => {
  global.fetch = mockFetch;
});

const mockFetch = jest.fn().mockImplementation(async (route: string) => {
  switch (route) {
    case 'pimee_job_automation_get_storage_connection_check':
      return {
        ok: true,
      };
    default:
      throw new Error();
  }
});

test('it renders the microsoft azure storage configurator', async () => {
  const storage: MicrosoftAzureStorage = {
    type: 'microsoft_azure',
    file_path: '/tmp/file.xlsx',
    connection_string: 'connection_string',
    container_name: 'container_name',
  };

  await act(async () => {
    renderWithProviders(
      <MicrosoftAzureStorageConfigurator
        jobInstanceCode="csv_product_export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={jest.fn()}
      />
    );
  });

  expect(screen.getByDisplayValue('/tmp/file.xlsx')).toBeInTheDocument();
  expect(screen.getByDisplayValue('connection_string')).toBeInTheDocument();
  expect(screen.getByDisplayValue('container_name')).toBeInTheDocument();
});

test('it allows user to fill file_path field', async () => {
  const storage: MicrosoftAzureStorage = {
    type: 'microsoft_azure',
    file_path: '/tmp/test.xls',
    connection_string: 'connection_string',
    container_name: 'container_name',
  };

  const onStorageChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <MicrosoftAzureStorageConfigurator
        jobInstanceCode="csv_product_export"
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

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, file_path: '/tmp/test.xlsx'});
});

test('it allows user to fill connection string field', async () => {
  const storage: MicrosoftAzureStorage = {
    type: 'microsoft_azure',
    file_path: '/tmp/file.xlsx',
    connection_string: 'connection_strin',
    container_name: 'container_name',
  };

  const onStorageChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <MicrosoftAzureStorageConfigurator
        jobInstanceCode="csv_product_export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={onStorageChange}
      />
    );
  });

  const connectionStringInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.connection_string.label pim_common.required_label'
  );
  userEvent.type(connectionStringInput, 'g');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, connection_string: 'connection_string'});
});

test('it allows user to fill container name field', async () => {
  const storage: MicrosoftAzureStorage = {
    type: 'microsoft_azure',
    file_path: '/tmp/file.xlsx',
    connection_string: 'connection_string',
    container_name: 'container_',
  };

  const onStorageChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <MicrosoftAzureStorageConfigurator
        jobInstanceCode="csv_product_export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={onStorageChange}
      />
    );
  });

  userEvent.paste(
    screen.getByLabelText(
      'pim_import_export.form.job_instance.storage_form.container_name.label pim_common.required_label'
    ),
    'name'
  );

  expect(onStorageChange).toHaveBeenLastCalledWith({
    ...storage,
    container_name: 'container_name',
  });
});

test('it throws an exception when passing a non microsoft azure storage', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  const storage: LocalStorage = {
    type: 'local',
    file_path: '/tmp/file.xlsx',
  };

  expect(() =>
    renderWithProviders(
      <MicrosoftAzureStorageConfigurator
        jobInstanceCode="csv_product_export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={jest.fn()}
      />
    )
  ).toThrowError('Invalid storage type "local" for microsoft azure storage configurator');

  mockedConsole.mockRestore();
});

test('it displays validation errors', async () => {
  const storage: MicrosoftAzureStorage = {
    type: 'microsoft_azure',
    file_path: '/tmp/file.xlsx',
    connection_string: 'connection_string',
    container_name: 'container_name',
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
      messageTemplate: 'error.key.a_connection_string_error',
      invalidValue: '',
      message: 'this is a connection string error',
      parameters: {},
      propertyPath: '[connection_string]',
    },
    {
      messageTemplate: 'error.key.a_container_name_error',
      invalidValue: '',
      message: 'this is a container name error',
      parameters: {},
      propertyPath: '[container_name]',
    },
  ];

  await act(async () => {
    renderWithProviders(
      <MicrosoftAzureStorageConfigurator
        jobInstanceCode="csv_product_export"
        storage={storage}
        fileExtension="xlsx"
        validationErrors={validationErrors}
        onStorageChange={jest.fn()}
      />
    );
  });

  expect(screen.getByText('error.key.a_file_path_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_connection_string_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_container_name_error')).toBeInTheDocument();
});

test('it can check connection', async () => {
  const storage: MicrosoftAzureStorage = {
    type: 'microsoft_azure',
    file_path: '/tmp/file.xlsx',
    connection_string: 'connection_string',
    container_name: 'container_name',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <MicrosoftAzureStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const checkButton = screen.getByText('pim_import_export.form.job_instance.connection_checker.label');
  await act(async () => {
    userEvent.click(checkButton);
  });

  expect(checkButton).toBeDisabled();
  expect(
    screen.queryByText('pim_import_export.form.job_instance.connection_checker.exception')
  ).not.toBeInTheDocument();
});

test('it cannot check connection if a field is empty', async () => {
  const storage: MicrosoftAzureStorage = {
    type: 'microsoft_azure',
    file_path: '/tmp/file.xlsx',
    connection_string: '',
    container_name: 'container_name',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <MicrosoftAzureStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const checkButton = screen.getByText('pim_import_export.form.job_instance.connection_checker.label');

  expect(checkButton).toBeDisabled();
});

test('it can check connection, display message if error', async () => {
  mockFetch.mockImplementation((route: string) => {
    switch (route) {
      case 'pimee_job_automation_get_storage_connection_check':
        return {
          ok: false,
        };
      default:
        throw new Error();
    }
  });

  const storage: MicrosoftAzureStorage = {
    type: 'microsoft_azure',
    file_path: '/tmp/file.xlsx',
    connection_string: 'connection_string',
    container_name: 'container_name',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <MicrosoftAzureStorageConfigurator
      jobInstanceCode="csv_product_export"
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const checkButton = screen.getByText('pim_import_export.form.job_instance.connection_checker.label');
  await act(async () => {
    userEvent.click(checkButton);
  });

  expect(checkButton).not.toBeDisabled();
  expect(screen.getByText('pim_import_export.form.job_instance.connection_checker.exception')).toBeInTheDocument();
});
