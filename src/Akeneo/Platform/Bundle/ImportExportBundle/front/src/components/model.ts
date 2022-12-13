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
  password: string | null;
};

type AmazonS3Storage = {
  type: 'amazon_s3';
  file_path: string;
  region: string;
  bucket: string;
  key: string;
  secret: string;
};

type GoogleCloudStorage = {
  type: 'google_cloud';
  file_path: string;
  project_id: string;
  service_account: string;
  bucket: string;
};

type NoneStorage = {
  type: 'none';
  file_path: string;
};

type Storage = LocalStorage | SftpStorage | AmazonS3Storage | GoogleCloudStorage | NoneStorage;

type StorageType = 'none' | 'local' | 'sftp' | 'amazon_s3' | 'google_cloud';

const STORAGE_TYPES = ['none', 'sftp', 'amazon_s3', 'google_cloud'];

const localStorageIsEnabled = (featureFlags: FeatureFlags): boolean =>
  featureFlags.isEnabled('job_automation_local_storage');

const getEnabledStorageTypes = (featureFlags: FeatureFlags): string[] => {
  const enabledStorageTypes = [...STORAGE_TYPES];

  if (localStorageIsEnabled(featureFlags)) {
    enabledStorageTypes.push('local');
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
    case 'google_cloud':
      return {
        type: 'google_cloud',
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
  GoogleCloudStorage,
  NoneStorage,
};
export {
  getDefaultStorage,
  isValidStorageType,
  isExport,
  getDefaultFilePath,
  getEnabledStorageTypes,
  localStorageIsEnabled,
};
