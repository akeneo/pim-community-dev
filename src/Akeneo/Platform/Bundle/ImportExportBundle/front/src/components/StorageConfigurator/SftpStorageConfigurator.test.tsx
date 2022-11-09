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
    fingerprint: 'c1:91:5e:42:55:5c:74:65:b6:12:32:7e:1f:6d:80:3e',
    port: 22,
    login_type: 'password',
    username: 'root',
    password: 'root',
  };

  renderWithProviders(
    <SftpStorageConfigurator storage={storage} fileExtension="xlsx" validationErrors={[]} onStorageChange={jest.fn()} />
  );

  expect(screen.getByDisplayValue('/tmp/file.xlsx')).toBeInTheDocument();
  expect(screen.getByDisplayValue('c1:91:5e:42:55:5c:74:65:b6:12:32:7e:1f:6d:80:3e')).toBeInTheDocument();
});

test('it allows user to fill file_path field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '/tmp/test.xls',
    host: '',
    port: 22,
    login_type: 'password',
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
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

test('it allows user to fill host field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: 'example.co',
    port: 22,
    login_type: 'password',
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
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
    host: 'example.com',
    port: 22,
    login_type: 'password',
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  userEvent.paste(
    screen.getByLabelText('pim_import_export.form.job_instance.storage_form.fingerprint.label'),
    'c1:91:5e:42:55:5c:74:65:b6:12:32:7e:1f:6d:80:3e'
  );

  expect(onStorageChange).toHaveBeenLastCalledWith({
    ...storage,
    fingerprint: 'c1:91:5e:42:55:5c:74:65:b6:12:32:7e:1f:6d:80:3e',
  });
});

test('it removes fingerprint from model when clearing input', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: 'example.com',
    fingerprint: 'c1:91:5e:42:55:5c:74:65:b6:12:32:7e:1f:6d:80:3e',
    port: 22,
    login_type: 'password',
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  userEvent.clear(screen.getByLabelText('pim_import_export.form.job_instance.storage_form.fingerprint.label'));

  expect(onStorageChange).toHaveBeenLastCalledWith({
    ...storage,
    fingerprint: undefined,
  });
});

test('it allows user to fill port field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: '',
    port: 2,
    login_type: 'password',
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const portInput = screen.getByLabelText(
    'pim_import_export.form.job_instance.storage_form.port.label pim_common.required_label'
  );
  userEvent.type(portInput, '2');

  expect(onStorageChange).toHaveBeenLastCalledWith({...storage, port: 22});
});

test('it allows user to change login type', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: 'example.com',
    port: 22,
    login_type: 'password',
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_import_export.form.job_instance.storage_form.login_type.label'));
  userEvent.click(
    screen.getByLabelText('pim_import_export.form.job_instance.storage_form.connection_type.private_key')
  );

  expect(onStorageChange).toHaveBeenLastCalledWith({
    ...storage,
    login_type: 'private_key',
  });
});

test('it displays a disabled public key field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: 'example.com',
    port: 22,
    login_type: 'private_key',
    username: '',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
  );

  const publicKeyField = screen.getByLabelText('pim_import_export.form.job_instance.storage_form.public_key.label');

  expect(publicKeyField).toBeDisabled();
});

test('it allows user to fill username field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '',
    host: '',
    port: 22,
    login_type: 'password',
    username: 'roo',
    password: '',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
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
    port: 22,
    login_type: 'password',
    username: '',
    password: 'roo',
  };

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
      storage={storage}
      fileExtension="xlsx"
      validationErrors={[]}
      onStorageChange={onStorageChange}
    />
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
    renderWithProviders(
      <SftpStorageConfigurator
        storage={storage}
        fileExtension="xlsx"
        validationErrors={[]}
        onStorageChange={jest.fn()}
      />
    )
  ).toThrowError('Invalid storage type "local" for sftp storage configurator');

  mockedConsole.mockRestore();
});

test('it displays validation errors', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '/tmp/file.xlsx',
    host: 'example.com',
    fingerprint: 'invalid',
    port: 22,
    login_type: 'password',
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
      messageTemplate: 'error.key.a_fingerprint_error',
      invalidValue: '',
      message: 'this is a fingerprint error',
      parameters: {},
      propertyPath: '[fingerprint]',
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
    <SftpStorageConfigurator
      storage={storage}
      fileExtension="xlsx"
      validationErrors={validationErrors}
      onStorageChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.a_file_path_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_host_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_fingerprint_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_port_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.an_username_error')).toBeInTheDocument();
  expect(screen.getByText('error.key.a_password_error')).toBeInTheDocument();
});

test('it can check connection', async () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '/tmp/file.xlsx',
    host: 'example.com',
    port: 22,
    login_type: 'password',
    username: 'root',
    password: 'root',
  };

  global.fetch = jest.fn().mockImplementation(async () => ({ok: true}));

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
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
});

test('it can check connection, display message if error', async () => {
  const storage: SftpStorage = {
    type: 'sftp',
    file_path: '/tmp/file.xlsx',
    host: 'example.com',
    port: 22,
    login_type: 'password',
    username: 'root',
    password: 'root',
  };

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
  }));

  const onStorageChange = jest.fn();

  renderWithProviders(
    <SftpStorageConfigurator
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
