import {FeatureFlags} from '@akeneo-pim-community/shared';

const SFTP_STORAGE_LOGIN_TYPES = ['password', 'private_key'] as const;

type SftpStorageLoginType = typeof SFTP_STORAGE_LOGIN_TYPES[number];

type JobType = 'import' | 'export';

type LocalStorage = {
  type: 'local';
  file_path: string;
};

type SftpPasswordStorage = {
  type: 'sftp';
  file_path: string;
  host: string;
  fingerprint?: string;
  port: number;
  username: string;
  login_type: 'password';
  password?: string;
};

type SftpPrivateKeyStorage = {
  type: 'sftp';
  file_path: string;
  host: string;
  fingerprint?: string;
  port: number;
  username: string;
  login_type: 'private_key';
};

type SftpStorage = SftpPasswordStorage | SftpPrivateKeyStorage;

type AmazonS3Storage = {
  type: 'amazon_s3';
  file_path: string;
  region: string;
  bucket: string;
  key: string;
  secret?: string;
};

type MicrosoftAzureStorage = {
  type: 'microsoft_azure';
  file_path: string;
  connection_string?: string;
  container_name: string;
};

type GoogleCloudStorage = {
  type: 'google_cloud_storage';
  file_path: string;
  project_id: string;
  service_account?: string;
  bucket: string;
};

type NoneStorage = {
  type: 'none';
  file_path: string;
};

type RemoteStorage = SftpStorage | AmazonS3Storage | MicrosoftAzureStorage | GoogleCloudStorage;

type Storage = LocalStorage | RemoteStorage | NoneStorage;

type StorageType = 'none' | 'local' | 'sftp' | 'amazon_s3' | 'microsoft_azure' | 'google_cloud_storage';

const STORAGE_TYPES = ['none'];

const localStorageIsEnabled = (featureFlags: FeatureFlags): boolean =>
  featureFlags.isEnabled('import_export_local_storage');

const additionalStorageIsEnabled = (featureFlags: FeatureFlags): boolean =>
  featureFlags.isEnabled('import_export_additional_storage');

const getEnabledStorageTypes = (featureFlags: FeatureFlags): string[] => {
  const enabledStorageTypes = [...STORAGE_TYPES];

  if (localStorageIsEnabled(featureFlags)) {
    enabledStorageTypes.push('local');
  }

  if (additionalStorageIsEnabled(featureFlags)) {
    enabledStorageTypes.push('sftp', 'amazon_s3', 'microsoft_azure', 'google_cloud_storage');
  }

  return enabledStorageTypes;
};

const isValidStorageType = (storageType: string, featureFlags: FeatureFlags): storageType is StorageType =>
  getEnabledStorageTypes(featureFlags).includes(storageType);

const isExport = (jobType: JobType) => 'export' === jobType;

const getDefaultFilePath = (jobType: JobType, fileExtension: string) =>
  isExport(jobType) ? `${jobType}_%job_label%_%datetime%.${fileExtension}` : `myfile.${fileExtension}`;

const isValidSftpLoginType = (loginType: string): loginType is SftpStorageLoginType =>
  SFTP_STORAGE_LOGIN_TYPES.includes(loginType as SftpStorageLoginType);

const getDefaultStorage = (jobType: JobType, storageType: StorageType, fileExtension: string): Storage => {
  switch (storageType) {
    case 'local':
      return {
        type: 'local',
        file_path: `/tmp/${getDefaultFilePath(jobType, fileExtension)}`,
      };
    case 'sftp':
      return {
        type: 'sftp',
        file_path: getDefaultFilePath(jobType, fileExtension),
        host: '',
        port: 22,
        login_type: 'password',
        username: '',
        password: '',
      };
    case 'amazon_s3':
      return {
        type: 'amazon_s3',
        file_path: getDefaultFilePath(jobType, fileExtension),
        region: '',
        bucket: '',
        key: '',
        secret: '',
      };
    case 'microsoft_azure':
      return {
        type: 'microsoft_azure',
        file_path: getDefaultFilePath(jobType, fileExtension),
        connection_string: '',
        container_name: '',
      };
    case 'google_cloud_storage':
      return {
        type: 'google_cloud_storage',
        file_path: getDefaultFilePath(jobType, fileExtension),
        project_id: '',
        service_account: '',
        bucket: '',
      };
    case 'none':
      return {
        type: 'none',
        file_path: getDefaultFilePath(jobType, fileExtension),
      };
    default:
      throw new Error(`Unknown storage type: ${storageType}`);
  }
};

const isLocalStorage = (storage: Storage): storage is LocalStorage =>
  'local' === storage.type && 'file_path' in storage;

const isSftpStorage = (storage: Storage): storage is SftpStorage => {
  return (
    'sftp' === storage.type &&
    'file_path' in storage &&
    'host' in storage &&
    'port' in storage &&
    'username' in storage &&
    'login_type' in storage &&
    isValidSftpLoginType(storage.login_type)
  );
};

const isSftpPasswordStorage = (sftpStorage: SftpStorage): sftpStorage is SftpPasswordStorage =>
  sftpStorage.login_type === 'password';

const isAmazonS3Storage = (storage: Storage): storage is AmazonS3Storage => {
  return (
    'amazon_s3' === storage.type &&
    'file_path' in storage &&
    'region' in storage &&
    'bucket' in storage &&
    'key' in storage
  );
};

const isMicrosoftAzureStorage = (storage: Storage): storage is MicrosoftAzureStorage => {
  return 'microsoft_azure' === storage.type && 'file_path' in storage && 'container_name' in storage;
};

const isGoogleCloudStorage = (storage: Storage): storage is GoogleCloudStorage => {
  return (
    'google_cloud_storage' === storage.type &&
    'file_path' in storage &&
    typeof 'file_path' === 'string' &&
    'project_id' in storage &&
    typeof 'project_id' === 'string' &&
    'bucket' in storage &&
    typeof 'bucket' === 'string'
  );
};

const isSftpConnectionFieldFulfilled = (storage: SftpStorage): boolean => {
  return (
    '' !== storage.file_path &&
    '' !== storage.host &&
    !isNaN(storage.port) &&
    '' !== storage.username &&
    (('password' === storage.login_type && '' !== storage.password) || 'private_key' === storage.login_type)
  );
};

const isAmazonS3ConnectionFieldFulfilled = (storage: AmazonS3Storage): boolean => {
  return (
    '' !== storage.file_path &&
    '' !== storage.region &&
    '' !== storage.bucket &&
    '' !== storage.key &&
    '' !== storage.secret
  );
};

const isMicrosoftAzureConnectionFieldFulfilled = (storage: MicrosoftAzureStorage): boolean => {
  return '' !== storage.connection_string && '' !== storage.container_name && '' !== storage.file_path;
};

const isGoogleCloudConnectionFieldFulfilled = (storage: GoogleCloudStorage): boolean => {
  return (
    '' !== storage.file_path && '' !== storage.project_id && '' !== storage.service_account && '' !== storage.bucket
  );
};

export type {
  AmazonS3Storage,
  GoogleCloudStorage,
  JobType,
  LocalStorage,
  MicrosoftAzureStorage,
  NoneStorage,
  RemoteStorage,
  SftpStorage,
  Storage,
  StorageType,
  SftpStorageLoginType,
};
export {
  additionalStorageIsEnabled,
  getDefaultFilePath,
  getDefaultStorage,
  getEnabledStorageTypes,
  isAmazonS3ConnectionFieldFulfilled,
  isAmazonS3Storage,
  isExport,
  isGoogleCloudConnectionFieldFulfilled,
  isGoogleCloudStorage,
  isLocalStorage,
  isMicrosoftAzureConnectionFieldFulfilled,
  isMicrosoftAzureStorage,
  isSftpConnectionFieldFulfilled,
  isSftpStorage,
  isValidSftpLoginType,
  isValidStorageType,
  localStorageIsEnabled,
  SFTP_STORAGE_LOGIN_TYPES,
  isSftpPasswordStorage,
};
