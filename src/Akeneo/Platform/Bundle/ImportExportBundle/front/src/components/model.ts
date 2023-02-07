import {FeatureFlags} from '@akeneo-pim-community/shared';
import {StorageLoginType} from './StorageConfigurator';

type JobType = 'import' | 'export';

type LocalStorage = {
  type: 'local';
  file_path: string;
};

type SftpStorage = {
  type: 'sftp';
  file_path: string;
  host: string;
  fingerprint?: string;
  port: number;
  login_type: StorageLoginType;
  username: string;
  password?: string | null;
};

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
  service_account: string;
  bucket: string;
};

type NoneStorage = {
  type: 'none';
  file_path: string;
};

type Storage = LocalStorage | SftpStorage | AmazonS3Storage | MicrosoftAzureStorage | GoogleCloudStorage | NoneStorage;

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

export type {
  JobType,
  Storage,
  StorageType,
  LocalStorage,
  SftpStorage,
  AmazonS3Storage,
  MicrosoftAzureStorage,
  GoogleCloudStorage,
  NoneStorage,
};
export {
  getDefaultStorage,
  isValidStorageType,
  isExport,
  getDefaultFilePath,
  getEnabledStorageTypes,
  additionalStorageIsEnabled,
  localStorageIsEnabled,
};
