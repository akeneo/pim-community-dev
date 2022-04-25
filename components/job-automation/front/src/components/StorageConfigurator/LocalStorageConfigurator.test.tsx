import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {LocalStorage, SftpStorage} from '../model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';

test('it renders the local storage configurator', () => {
  const storage: LocalStorage = {
    type: 'local',
    filePath: '/tmp/file.csv',
  };

  renderWithProviders(<LocalStorageConfigurator storage={storage} onChange={jest.fn()} />);

  expect(screen.getByDisplayValue('/tmp/file.csv')).toBeInTheDocument();
});

test('it allows user to fill local storage filePath field', () => {
  const storage: LocalStorage = {
    type: 'local',
    filePath: '/tmp/file.cs',
  };
  const onChange = jest.fn();

  renderWithProviders(<LocalStorageConfigurator storage={storage} onChange={onChange} />);

  const filePathInput = screen.getByLabelText('akeneo.automation.storage.file_path.label');
  userEvent.type(filePathInput, 'v');

  expect(onChange).toHaveBeenLastCalledWith({
    type: 'local',
    filePath: '/tmp/file.csv',
  });
});

test('it throws an exception when passing a non-local storage', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  const storage: SftpStorage = {
    type: 'sftp',
    filePath: '/tmp/file.txt',
    host: 'localhost',
    username: 'root',
    password: 'root',
  };

  expect(() => renderWithProviders(<LocalStorageConfigurator storage={storage} onChange={jest.fn()} />)).toThrowError(
    'Invalid storage type "sftp" for local storage configurator'
  );

  mockedConsole.mockRestore();
});
