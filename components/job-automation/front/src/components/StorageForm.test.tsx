import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {StorageForm} from './StorageForm';
import {LocalStorage, NoneStorage, SftpStorage} from './model';

test('it renders the storage form', () => {
  const storage: NoneStorage = {
    type: 'none',
  };

  renderWithProviders(<StorageForm storage={storage} onChange={jest.fn()} />);

  expect(screen.getByText('akeneo.automation.storage.connection.none')).toBeInTheDocument();
});

test('it triggers onChange callback when storage configurator onChange is triggered', () => {
  const storage: LocalStorage = {
    type: 'local',
    filePath: '/tmp/file.cs',
  };

  const onChange = jest.fn();

  renderWithProviders(<StorageForm storage={storage} onChange={onChange} />);

  const filePathInput = screen.getByLabelText('akeneo.automation.storage.file_path.label');
  userEvent.type(filePathInput, 'v');

  expect(onChange).toHaveBeenLastCalledWith({
    type: 'local',
    filePath: '/tmp/file.csv',
  });
});

test('it does not render the storage form configurator if storage is none', () => {
  const storage: NoneStorage = {
    type: 'none',
  };

  renderWithProviders(<StorageForm storage={storage} onChange={jest.fn()} />);

  expect(screen.queryByText('akeneo.automation.storage.file_path.label')).not.toBeInTheDocument();
});

test('it renders the storage form configurator if storage is local', () => {
  const storage: LocalStorage = {
    type: 'local',
    filePath: '',
  };

  renderWithProviders(<StorageForm storage={storage} onChange={jest.fn()} />);

  expect(screen.getByText('akeneo.automation.storage.file_path.label')).toBeInTheDocument();
  expect(screen.queryByText('akeneo.automation.storage.host.label')).not.toBeInTheDocument();
});

test('it renders the storage form configurator if storage is sftp', () => {
  const storage: SftpStorage = {
    type: 'sftp',
    filePath: '',
    host: '',
    username: '',
    password: '',
  };

  renderWithProviders(<StorageForm storage={storage} onChange={jest.fn()} />);

  expect(screen.getByText('akeneo.automation.storage.file_path.label')).toBeInTheDocument();
  expect(screen.getByText('akeneo.automation.storage.host.label')).toBeInTheDocument();
});

test('it can select a local storage', () => {
  const storage: NoneStorage = {
    type: 'none',
  };

  const onChange = jest.fn();

  renderWithProviders(<StorageForm storage={storage} onChange={onChange} />);

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.automation.storage.connection.local'));

  expect(onChange).toBeCalledWith({
    type: 'local',
    filePath: '',
  });
});

test('it can select a sftp storage', () => {
  const storage: NoneStorage = {
    type: 'none',
  };

  const onChange = jest.fn();

  renderWithProviders(<StorageForm storage={storage} onChange={onChange} />);

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.automation.storage.connection.sftp'));

  expect(onChange).toBeCalledWith({
    type: 'sftp',
    filePath: '',
    host: '',
    username: '',
    password: '',
  });
});
