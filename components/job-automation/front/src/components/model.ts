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

const getDefaultStorage = (storageType: StorageType): Storage => {
  switch (storageType) {
    case 'local':
      return {
        type: 'local',
        file_path: '',
      };
    case 'sftp':
      return {
        type: 'sftp',
        file_path: '',
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

export type {Storage, StorageType, LocalStorage, SftpStorage, NoneStorage};

export {getDefaultStorage, isValidStorageType, STORAGE_TYPES};
