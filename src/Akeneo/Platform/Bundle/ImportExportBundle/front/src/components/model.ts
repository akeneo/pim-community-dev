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

type NoneStorage = {
  type: 'none';
};

type Storage = LocalStorage | SftpStorage | NoneStorage;

type StorageType = 'none' | 'local' | 'sftp';

const STORAGE_TYPES = ['none'];

const getEnabledStorageTypes = (featureFlags: FeatureFlags): string[] => {
  const enabledStorageTypes = [...STORAGE_TYPES];

  if (featureFlags.isEnabled('job_automation_local_storage')) {
    enabledStorageTypes.push('local');
  }

  if (featureFlags.isEnabled('job_automation_remote_storage')) {
    enabledStorageTypes.push('sftp');
  }

  return enabledStorageTypes;
};

const isValidStorageType = (storageType: string, featureFlags: FeatureFlags): storageType is StorageType => {
  const enabledStorageTypes = getEnabledStorageTypes(featureFlags);
  return enabledStorageTypes.includes(storageType);
};

const isExport = (jobType: JobType) => 'export' === jobType;

const getDefaultFilePath = (jobType: JobType, fileExtension: string) => {
  return isExport(jobType) ? `${jobType}_%job_label%_%datetime%.${fileExtension}` : `myfile.${fileExtension}`;
};

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
    case 'none':
      return {
        type: 'none',
      };
    default:
      throw new Error(`Unknown storage type: ${storageType}`);
  }
};

export type {JobType, Storage, StorageType, LocalStorage, SftpStorage, NoneStorage};

export {getDefaultStorage, isValidStorageType, isExport, getDefaultFilePath, getEnabledStorageTypes};
