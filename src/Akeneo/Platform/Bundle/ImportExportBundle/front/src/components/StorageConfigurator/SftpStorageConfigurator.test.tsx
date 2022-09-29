import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen, act} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {LocalStorage, SftpStorage} from '../model';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';

test('it renders the sftp storage configurator', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '/tmp/file.xlsx',
    host: 'example.com',
    fingerprint: '8653a85f112ca9286bd78fbe47606a23',
    port: 22,
    username: 'root',
    password: 'root',
  };

  renderWithProviders(<SftpStorageConfigurator storage={storage} validationErrors={[]} onStorageChange={jest.fn()} />);

  expect(screen.getByDisplayValue('/tmp/file.xlsx')).toBeInTheDocument();
});

test('it allows user to fill file_path field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '/tmp/test.xls',
    host: '',
    fingerprint: '',
    port: 22,
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator storage={storage} validationErrors={[]} onStorageChange={onStorageChange} />
  );

  const file_pathInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.file_path.label pim_common.required_label'
  );
  userEvent.type(file_pathInput, 'x');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, file_path: '/tmp/test.xlsx'});
});

test('it allows user to fill host field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: 'example.co',
    fingerprint: '',
    port: 22,
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator storage={storage} validationErrors={[]} onStorageChange={onStorageChange} />
  );

  const hostInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.host.label pim_common.required_label'
  );
  userEvent.type(hostInput, 'm');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, host: 'example.com'});
});

test('it allows user to fill fingerprint field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: 'example.co',
    fingerprint: '',
    port: 22,
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator storage={storage} validationErrors={[]} onStorageChange={onStorageChange} />
  );

  const hostInput = screen.getByLabelText('pim_import_export.form.job_instance.storage_form.fingerprint.label');
  userEvent.paste(hostInput, '8653a85f112ca9286bd78fbe47606a23');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, fingerprint: '8653a85f112ca9286bd78fbe47606a23'});
});

test('it allows user to fill port field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: '',
    fingerprint: '',
    port: 2,
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator storage={storage} validationErrors={[]} onStorageChange={onStorageChange} />
  );

  const portInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.port.label pim_common.required_label'
  );
  userEvent.type(portInput, '2');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, port: 22});
});

test('it allows user to fill username field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: '',
    fingerprint: '',
    port: 22,
    username: 'roo',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator storage={storage} validationErrors={[]} onStorageChange={onStorageChange} />
  );

  const usernameInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.username.label pim_common.required_label'
  );
  userEvent.type(usernameInput, 't');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, username: 'root'});
});

test('it allows user to fill password field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: '',
    fingerprint: '',
    port: 22,
    username: '',
    password: 'roo',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator storage={storage} validationErrors={[]} onStorageChange={onStorageChange} />
  );

  const passwordInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.password.label pim_common.required_label'
  );
  userEvent.type(passwordInput, 't');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, password: 'root'});
});

test('it throws an exception when passing a non-sftp storage', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  const storage: LocalStorage = {
    type: 'local',
    file_path: '/tmp/file.xlsx',
  };

  expect(() =>
    renderWithProviders(<SftpStorageConfigurator storage={storage} validationErrors={[]} onStorageChange={jest.fn()} />)
  ).toThrowError('Invalid storage type "local" for sftp storage configurator');

  mockedConsole.mockRestore();
});

test('it displays validation errors', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '/tmp/file.xlsx',
    host: 'example.com',
    fingerprint: '',
    port: 22,
    username: 'root',
    password: 'root',
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
      messageTemplate: 'error.key.a_host_error',
      invalidValue: '',
      message: 'this is a host error',
      parameters: {},
      propertyPath: '[host]',
    },
    {
      messageTemplate: 'error.key.a_port_error',
      invalidValue: '',
      message: 'this is a port error',
      parameters: {},
      propertyPath: '[port]',
    },
    {
      messageTemplate: 'error.key.an_username_error',
      invalidValue: '',
      message: 'this is an username error',
      parameters: {},
      propertyPath: '[username]',
    },
    {
      messageTemplate: 'error.key.a_password_error',
      invalidValue: '',
      message: 'this is a password error',
      parameters: {},
      propertyPath: '[password]',
    },
  ];

  renderWithProviders(
    <SftpStorageConfigurator storage={storage} validationErrors={validationErrors} onStorageChange={jest.fn()} />
  );

  expect(screen.getByText('error.key.a_file_path_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_host_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_port_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.an_username_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_password_error')).toBeInTheDocument();
});

test('it can check connection', async () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '/tmp/file.xlsx',
    host: 'example.com',
    fingerprint: '',
    port: 22,
    username: 'root',
    password: 'root',
  };

  global.fetch = jest.fn().mockImplementation(async () => ({ok: true}));

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator storage={storage} validationErrors={[]} onStorageChange={onStorageChange} />
  );

  const checkButton = screen.getByText('pim_import_export.form.job_instance.connection_checker.label');
  await act(async () => {
    userEvent.click(checkButton);
  });

  expect(checkButton).toBeDisabled();
});

test('it can check connection, display message if error', async () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '/tmp/file.xlsx',
    host: 'example.com',
    fingerprint: '',
    port: 22,
    username: 'root',
    password: 'root',
  };

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
  }));

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator storage={storage} validationErrors={[]} onStorageChange={onStorageChange} />
  );

  const checkButton = screen.getByText('pim_import_export.form.job_instance.connection_checker.label');
  await act(async () => {
    userEvent.click(checkButton);
  });

  expect(checkButton).not.toBeDisabled();
  expect(screen.getByText('pim_import_export.form.job_instance.connection_checker.exception')).toBeInTheDocument();
});
