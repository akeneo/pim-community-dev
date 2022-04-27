import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {LocalStorage, SftpStorage} from '../model';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';

test('it renders the sftp storage configurator', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    filePath: '/tmp/file.xlsx',
    host: 'localhost',
    username: 'root',
    password: 'root',
  };

  renderWithProviders(<SftpStorageConfigurator storage={storage} onChange={jest.fn()} />);

  expect(screen.getByDisplayValue('/tmp/file.xlsx')).toBeInTheDocument();
});

test('it allows user to fill filePath field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    filePath: '/tmp/test.cs',
    host: '',
    username: '',
    password: '',
  };

  const onChange = jest.fn();

  renderWithProviders(<SftpStorageConfigurator storage={storage} onChange={onChange} />);

  const filePathInput = screen.getByLabelText('akeneo.automation.storage.file_path.label');
  userEvent.type(filePathInput, 'v');

  expect(onChange).toHaveBeenLastCalledWith({...storage, filePath: '/tmp/test.csv'});
});

test('it allows user to fill host field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    filePath: '',
    host: 'localhos',
    username: '',
    password: '',
  };

  const onChange = jest.fn();

  renderWithProviders(<SftpStorageConfigurator storage={storage} onChange={onChange} />);

  const hostInput = screen.getByLabelText('akeneo.automation.storage.host.label');
  userEvent.type(hostInput, 't');

  expect(onChange).toHaveBeenLastCalledWith({...storage, host: 'localhost'});
});

test('it allows user to fill username field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    filePath: '',
    host: '',
    username: 'roo',
    password: '',
  };

  const onChange = jest.fn();

  renderWithProviders(<SftpStorageConfigurator storage={storage} onChange={onChange} />);

  const usernameInput = screen.getByLabelText('akeneo.automation.storage.username.label');
  userEvent.type(usernameInput, 't');

  expect(onChange).toHaveBeenLastCalledWith({...storage, username: 'root'});
});

test('it allows user to fill password field', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    filePath: '',
    host: '',
    username: '',
    password: 'roo',
  };

  const onChange = jest.fn();

  renderWithProviders(<SftpStorageConfigurator storage={storage} onChange={onChange} />);

  const passwordInput = screen.getByLabelText('akeneo.automation.storage.password.label');
  userEvent.type(passwordInput, 't');

  expect(onChange).toHaveBeenLastCalledWith({...storage, password: 'root'});
});

test('it throws an exception when passing a non-sftp storage', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  const storage: LocalStorage = {
    type: 'local',
    filePath: '/tmp/file.csv',
  };

  expect(() => renderWithProviders(<SftpStorageConfigurator storage={storage} onChange={jest.fn()} />)).toThrowError(
    'Invalid storage type "local" for sftp storage configurator'
  );

  mockedConsole.mockRestore();
});
