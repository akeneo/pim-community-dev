import {FeatureFlags} from '@akeneo-pim-community/shared';
import {LocalStorage, SftpStorage} from '../model';
import {isLocalStorage, isSftpStorage, getStorageConfigurator} from './model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';

const featureFlagCollection = {
  job_automation_local_storage: false,
};

const enableFeatureFlag = (featureFlag: string) => (featureFlagCollection[featureFlag] = true);

const featureFlags: FeatureFlags = {
  isEnabled: (featureFlag: string) => featureFlagCollection[featureFlag],
};

const localStorage: LocalStorage = {
  type: 'local',
  file_path: '/tmp/test.xlsx',
};

const sftpStorage: SftpStorage = {
  type: 'sftp',
  host: 'example.com',
  port: 22,
  username: 'test',
  password: 'test',
  file_path: '/tmp/test.xlsx',
};

test('it says if a storage is a local storage', () => {
  expect(isLocalStorage(localStorage)).toBe(true);
  expect(isLocalStorage(sftpStorage)).toBe(false);
});

test('it says if a storage is a sftp storage', () => {
  expect(isSftpStorage(sftpStorage)).toBe(true);
  expect(isSftpStorage({...sftpStorage, fingerprint: 'c1:91:5e:42:55:5c:74:65:b6:12:32:7e:1f:6d:80:3e'})).toBe(true);
  expect(isSftpStorage(localStorage)).toBe(false);
});

test('it returns storage configurator', () => {
  expect(getStorageConfigurator('none', featureFlags)).toBe(null);

  expect(getStorageConfigurator('local', featureFlags)).toBe(null);
  enableFeatureFlag('job_automation_local_storage');
  expect(getStorageConfigurator('local', featureFlags)).toBe(LocalStorageConfigurator);

  expect(getStorageConfigurator('sftp', featureFlags)).toBe(SftpStorageConfigurator);

  // @ts-expect-error - there is no storage configurator for type 'unknown'
  expect(getStorageConfigurator('unknown', featureFlags)).toBe(null);
});
