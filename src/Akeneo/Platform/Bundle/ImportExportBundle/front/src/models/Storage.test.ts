import {FeatureFlags} from '@akeneo-pim-community/shared';
import {
  isValidStorageType,
  getDefaultStorage,
  isExport,
  getDefaultFilePath,
  localStorageIsEnabled,
  additionalStorageIsEnabled,
  isAmazonS3Storage,
  isGoogleCloudStorage,
  isLocalStorage,
  isMicrosoftAzureStorage,
  isSftpStorage,
  AmazonS3Storage,
  GoogleCloudStorage,
  LocalStorage,
  MicrosoftAzureStorage,
  SftpStorage,
  isSftpConnectionFieldFulfilled,
  isGoogleCloudConnectionFieldFulfilled,
  isAmazonS3ConnectionFieldFulfilled,
  isMicrosoftAzureConnectionFieldFulfilled,
} from './Storage';

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

const featureFlagCollection = {
  import_export_local_storage: false,
  import_export_additional_storage: false,
};

const enableFeatureFlag = (featureFlag: string) => (featureFlagCollection[featureFlag] = true);

const featureFlags: FeatureFlags = {
  isEnabled: (featureFlag: string) => featureFlagCollection[featureFlag],
};

beforeEach(() => {
  featureFlagCollection.import_export_local_storage = false;
  featureFlagCollection.import_export_additional_storage = false;
});

test('it says if a storage type is valid', () => {
  expect(isValidStorageType('local', featureFlags)).toBe(false);
  expect(isValidStorageType('sftp', featureFlags)).toBe(false);
  expect(isValidStorageType('amazon_s3', featureFlags)).toBe(false);
  expect(isValidStorageType('microsoft_azure', featureFlags)).toBe(false);

  enableFeatureFlag('import_export_local_storage');
  expect(isValidStorageType('local', featureFlags)).toBe(true);
  expect(isValidStorageType('sftp', featureFlags)).toBe(false);
  expect(isValidStorageType('amazon_s3', featureFlags)).toBe(false);
  expect(isValidStorageType('microsoft_azure', featureFlags)).toBe(false);
  expect(isValidStorageType('google_cloud_storage', featureFlags)).toBe(false);

  enableFeatureFlag('import_export_additional_storage');
  expect(isValidStorageType('none', featureFlags)).toBe(true);
  expect(isValidStorageType('local', featureFlags)).toBe(true);
  expect(isValidStorageType('sftp', featureFlags)).toBe(true);
  expect(isValidStorageType('amazon_s3', featureFlags)).toBe(true);
  expect(isValidStorageType('microsoft_azure', featureFlags)).toBe(true);
  expect(isValidStorageType('google_cloud_storage', featureFlags)).toBe(true);
  expect(isValidStorageType('invalid', featureFlags)).toBe(false);
});

test('it returns the default local storage', () => {
  expect(getDefaultStorage('export', 'local', 'xlsx')).toEqual({
    type: 'local',
    file_path: '/tmp/export_%job_label%_%datetime%.xlsx',
  });

  expect(getDefaultStorage('import', 'sftp', 'csv')).toEqual({
    type: 'sftp',
    file_path: 'myfile.csv',
    host: '',
    login_type: 'password',
    port: 22,
    username: '',
    password: '',
  });

  expect(getDefaultStorage('import', 'amazon_s3', 'csv')).toEqual({
    type: 'amazon_s3',
    file_path: 'myfile.csv',
    region: '',
    bucket: '',
    key: '',
    secret: '',
  });

  expect(getDefaultStorage('import', 'microsoft_azure', 'csv')).toEqual({
    type: 'microsoft_azure',
    file_path: 'myfile.csv',
    connection_string: '',
    container_name: '',
  });

  expect(getDefaultStorage('import', 'google_cloud_storage', 'csv')).toEqual({
    type: 'google_cloud_storage',
    file_path: 'myfile.csv',
    project_id: '',
    service_account: '',
    bucket: '',
  });

  expect(getDefaultStorage('export', 'none', 'xlsx')).toEqual({
    type: 'none',
    file_path: 'export_%job_label%_%datetime%.xlsx',
  });

  // @ts-expect-error invalid storage type
  expect(() => getDefaultStorage('export', 'invalid', 'xlsx')).toThrowError('Unknown storage type: invalid');
});

test('it says if a job is an export', () => {
  expect(isExport('export')).toBe(true);
  expect(isExport('import')).toBe(false);
});

test('it returns the default file path', () => {
  expect(getDefaultFilePath('export', 'xlsx')).toBe('export_%job_label%_%datetime%.xlsx');
  expect(getDefaultFilePath('export', 'csv')).toBe('export_%job_label%_%datetime%.csv');
  expect(getDefaultFilePath('import', 'xlsx')).toBe('myfile.xlsx');
  expect(getDefaultFilePath('import', 'csv')).toBe('myfile.csv');
});

test('it check if local storage is enabled', () => {
  expect(localStorageIsEnabled(featureFlags)).toBe(false);
  enableFeatureFlag('import_export_local_storage');
  expect(localStorageIsEnabled(featureFlags)).toBe(true);
});

test('it check if additionnal storage is enabled', () => {
  expect(additionalStorageIsEnabled(featureFlags)).toBe(false);
  enableFeatureFlag('import_export_additional_storage');
  expect(additionalStorageIsEnabled(featureFlags)).toBe(true);
});

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

test('it can check that a SFTP storage is filled', () => {
  expect(isSftpConnectionFieldFulfilled(sftpStorage)).toBe(true);
});

test('it can check that a GCS storage is filled', () => {
  expect(isGoogleCloudConnectionFieldFulfilled(googleCloudStorage)).toBe(true);
});

test('it can check that a Amazon S3 storage is filled', () => {
  expect(isAmazonS3ConnectionFieldFulfilled(amazonS3Storage)).toBe(true);
});

test('it can check that a Azure storage is filled', () => {
  expect(isMicrosoftAzureConnectionFieldFulfilled(microsoftAzureStorage)).toBe(true);
});
