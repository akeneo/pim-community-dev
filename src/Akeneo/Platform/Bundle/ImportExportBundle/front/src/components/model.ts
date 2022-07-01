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

type StorageType = Storage['type'];

const STORAGE_TYPES = ['local', 'sftp', 'none'];

const isValidStorageType = (storageType: string): storageType is StorageType => {
  return STORAGE_TYPES.includes(storageType);
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

export {getDefaultStorage, isValidStorageType, STORAGE_TYPES, isExport, getDefaultFilePath};
