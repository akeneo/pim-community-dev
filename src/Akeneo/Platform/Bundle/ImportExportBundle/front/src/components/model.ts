import {FeatureFlags} from '@akeneo-pim-community/shared';

type JobType = 'import' | 'export';

type LocalStorage = {
  type: 'local';
  file_path: string;
};

type SftpStorage = {
  type: 'sftp';
  file_path: string;
  host: string;
  port: number;
  username: string;
  password: string;
};

type AmazonS3Storage = {
  type: 'amazon_s3';
  file_path: string;
  region: string;
  bucket_name: string;
  key: string;
  secret: string;
};

type AzureBlobStorage = {
  type: 'azure_blob';
  file_path: string;
  connection_string: string;
  container_name: string;
};

type NoneStorage = {
  type: 'none';
  file_path: string;
};

type Storage = LocalStorage | SftpStorage | AmazonS3Storage | AzureBlobStorage | NoneStorage;

type StorageType = 'none' | 'local' | 'sftp' | 'amazon_s3' | 'azure_blob';

const STORAGE_TYPES = ['none', 'sftp'];

const localStorageIsEnabled = (featureFlags: FeatureFlags): boolean =>
  featureFlags.isEnabled('job_automation_local_storage');

const getEnabledStorageTypes = (featureFlags: FeatureFlags): string[] => {
  const enabledStorageTypes = [...STORAGE_TYPES];

  if (localStorageIsEnabled(featureFlags)) {
    enabledStorageTypes.push('local');
  }

  if (remoteStorageIsEnabled(jobCode)) {
    enabledStorageTypes.push('sftp');
    enabledStorageTypes.push('amazon_s3');
    enabledStorageTypes.push('azure_blob');
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
        file_path: getDefaultFilePath(jobType, fileExtension),
      };
    case 'sftp':
      return {
        type: 'sftp',
        file_path: getDefaultFilePath(jobType, fileExtension),
        host: '',
        port: 22,
        username: '',
        password: '',
      };
    case 'amazon_s3':
      return {
        type: 'amazon_s3',
        file_path: getDefaultFilePath(jobType, fileExtension),
        region: '',
        bucket_name: '',
        key: '',
        secret: '',
      };
    case 'azure_blob':
      return {
        type: 'azure_blob',
        file_path: getDefaultFilePath(jobType, fileExtension),
        connection_string: '',
        container_name: '',
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

export type {JobType, Storage, StorageType, LocalStorage, SftpStorage, AmazonS3Storage, AzureBlobStorage, NoneStorage};
export {
  getDefaultStorage,
  isValidStorageType,
  isExport,
  getDefaultFilePath,
  getEnabledStorageTypes,
  localStorageIsEnabled,
};
