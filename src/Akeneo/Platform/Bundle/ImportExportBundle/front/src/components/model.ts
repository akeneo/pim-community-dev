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

const REMOTE_STORAGE_JOB_CODES = [
  'xlsx_product_export',
  'xlsx_product_import',
  'xlsx_tailored_product_export',
  'xlsx_tailored_product_import',
];

const localStorageIsEnabled = (featureFlags: FeatureFlags): boolean =>
  featureFlags.isEnabled('job_automation_local_storage');

const remoteStorageIsEnabled = (featureFlags: FeatureFlags, jobCode: string): boolean =>
  REMOTE_STORAGE_JOB_CODES.includes(jobCode);

const shouldHideForm = (featureFlags: FeatureFlags, jobCode: string): boolean =>
  !localStorageIsEnabled(featureFlags) && !remoteStorageIsEnabled(featureFlags, jobCode);

const getEnabledStorageTypes = (featureFlags: FeatureFlags, jobCode: string): string[] => {
  const enabledStorageTypes = [...STORAGE_TYPES];

  if (localStorageIsEnabled(featureFlags)) {
    enabledStorageTypes.push('local');
  }

  if (remoteStorageIsEnabled(featureFlags, jobCode)) {
    enabledStorageTypes.push('sftp');
  }

  return enabledStorageTypes;
};

const isValidStorageType = (
  storageType: string,
  featureFlags: FeatureFlags,
  jobCode: string
): storageType is StorageType => {
  const enabledStorageTypes = getEnabledStorageTypes(featureFlags, jobCode);
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

export {
  getDefaultStorage,
  isValidStorageType,
  isExport,
  getDefaultFilePath,
  getEnabledStorageTypes,
  localStorageIsEnabled,
  remoteStorageIsEnabled,
  shouldHideForm,
};
