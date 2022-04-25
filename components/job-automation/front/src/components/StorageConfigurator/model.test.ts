import {LocalStorage, SftpStorage} from '../model';
import {isLocalStorage, isSftpStorage, getStorageConfigurator} from './model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';

const localStorage: LocalStorage = {
  type: 'local',
  filePath: '/tmp/test.xlsx',
};

const sftpStorage: SftpStorage = {
  type: 'sftp',
  host: 'sftp://localhost',
  username: 'test',
  password: 'test',
  filePath: '/tmp/test.xlsx',
};

test('it says if a storage is a local storage', () => {
  expect(isLocalStorage(localStorage)).toBe(true);
  expect(isLocalStorage(sftpStorage)).toBe(false);
});

test('it says if a storage is a sftp storage', () => {
  expect(isSftpStorage(sftpStorage)).toBe(true);
  expect(isSftpStorage(localStorage)).toBe(false);
});

test('it returns storage configurator', () => {
  expect(getStorageConfigurator('none')).toBe(null);
  expect(getStorageConfigurator('local')).toBe(LocalStorageConfigurator);
  expect(getStorageConfigurator('sftp')).toBe(SftpStorageConfigurator);

  // @ts-expect-error - there is no storage configurator for type 'unknown'
  expect(getStorageConfigurator('unknown')).toBe(null);
});
