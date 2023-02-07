import {FeatureFlags} from '@akeneo-pim-community/shared';
import {AmazonS3Storage, GoogleCloudStorage, LocalStorage, MicrosoftAzureStorage, SftpStorage} from '../model';
import {
  isLocalStorage,
  isSftpStorage,
  getStorageConfigurator,
  isAmazonS3Storage,
  isMicrosoftAzureStorage,
  isGoogleCloudStorage,
} from './model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';
import {AmazonS3StorageConfigurator} from './AmazonS3StorageConfigurator';
import {GoogleCloudStorageConfigurator} from './GoogleCloudStorageConfigurator';
import {MicrosoftAzureStorageConfigurator} from './MicrosoftAzureStorageConfigurator';

const featureFlagCollection = {
  import_export_local_storage: false,
  import_export_additional_storage: false,
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
  login_type: 'password',
  username: 'test',
  password: 'test',
  file_path: '/tmp/test.xlsx',
};

const amazonS3Storage: AmazonS3Storage = {
  type: 'amazon_s3',
  region: 'eu-west-3',
  bucket: 'a_bucket',
  key: 'test',
  secret: 'test',
  file_path: '/tmp/test.xlsx',
};

const microsoftAzureStorage: MicrosoftAzureStorage = {
  type: 'microsoft_azure',
  connection_string: 'agagag',
  container_name: 'ahaha',
  file_path: '/tmp/test.xlsx',
};

const googleCloudStorage: GoogleCloudStorage = {
  type: 'google_cloud_storage',
  file_path: '/tmp/test.xlsx',
  project_id: 'eu-west-3',
  service_account: '{"type": "service_account"}',
  bucket: 'a_bucket',
};

test('it says if a storage is a local storage', () => {
  expect(isLocalStorage(localStorage)).toBe(true);
  expect(isLocalStorage(sftpStorage)).toBe(false);
  expect(isLocalStorage(amazonS3Storage)).toBe(false);
});

test('it says if a storage is a sftp storage', () => {
  expect(isSftpStorage(sftpStorage)).toBe(true);
  expect(isSftpStorage({...sftpStorage, fingerprint: 'c1:91:5e:42:55:5c:74:65:b6:12:32:7e:1f:6d:80:3e'})).toBe(true);
  expect(isSftpStorage(amazonS3Storage)).toBe(false);
  expect(isSftpStorage(localStorage)).toBe(false);
});

test('it says if a storage is an amazon s3 storage', () => {
  expect(isAmazonS3Storage(amazonS3Storage)).toBe(true);
  expect(isAmazonS3Storage(sftpStorage)).toBe(false);
  expect(isSftpStorage(localStorage)).toBe(false);
});

test('it says if a storage is a microsoft azure storage', () => {
  expect(isMicrosoftAzureStorage(microsoftAzureStorage)).toBe(true);
  expect(isMicrosoftAzureStorage(sftpStorage)).toBe(false);
  expect(isSftpStorage(localStorage)).toBe(false);
});

test('it says if a storage is a google cloud storage', () => {
  expect(isGoogleCloudStorage(googleCloudStorage)).toBe(true);
  expect(isGoogleCloudStorage(sftpStorage)).toBe(false);
  expect(isSftpStorage(localStorage)).toBe(false);
});

test('it returns storage configurator', () => {
  expect(getStorageConfigurator('none', featureFlags)).toBe(null);
  expect(getStorageConfigurator('local', featureFlags)).toBe(null);

  enableFeatureFlag('import_export_local_storage');
  expect(getStorageConfigurator('local', featureFlags)).toBe(LocalStorageConfigurator);
  expect(getStorageConfigurator('sftp', featureFlags)).toBe(null);
  expect(getStorageConfigurator('amazon_s3', featureFlags)).toBe(null);
  expect(getStorageConfigurator('microsoft_azure', featureFlags)).toBe(null);
  expect(getStorageConfigurator('google_cloud_storage', featureFlags)).toBe(null);

  enableFeatureFlag('import_export_additional_storage');
  expect(getStorageConfigurator('sftp', featureFlags)).toBe(SftpStorageConfigurator);
  expect(getStorageConfigurator('amazon_s3', featureFlags)).toBe(AmazonS3StorageConfigurator);
  expect(getStorageConfigurator('microsoft_azure', featureFlags)).toBe(MicrosoftAzureStorageConfigurator);
  expect(getStorageConfigurator('google_cloud_storage', featureFlags)).toBe(GoogleCloudStorageConfigurator);

  // @ts-expect-error - there is no storage configurator for type 'unknown'
  expect(getStorageConfigurator('unknown', featureFlags)).toBe(null);
});
