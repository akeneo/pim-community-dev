type LocalStorage = {
  type: 'local';
  filePath: string;
};

type SftpStorage = {
  type: 'sftp';
  filePath: string;
  host: string;
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
        filePath: '',
      };
    case 'sftp':
      return {
        type: 'sftp',
        filePath: '',
        host: '',
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
