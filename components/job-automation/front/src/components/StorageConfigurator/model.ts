import {FunctionComponent} from 'react';
import {LocalStorage, SftpStorage, Storage, StorageType} from '../model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';

type StorageConfiguratorProps = {
  storage: Storage;
  onChange: (storage: Storage) => void;
};

const storageConfigurators: {
  [storageType in StorageType]: FunctionComponent<StorageConfiguratorProps> | null;
} = {
  local: LocalStorageConfigurator,
  sftp: SftpStorageConfigurator,
  none: null,
};

const getStorageConfigurator = (storageType: StorageType): FunctionComponent<StorageConfiguratorProps> | null => {
  return storageConfigurators[storageType] ?? null;
};

const isLocalStorage = (storage: Storage): storage is LocalStorage => {
  return 'local' === storage.type && 'filePath' in storage;
};

const isSftpStorage = (storage: Storage): storage is SftpStorage => {
  return (
    'sftp' === storage.type &&
    'filePath' in storage &&
    'host' in storage &&
    'username' in storage &&
    'password' in storage
  );
};

export type {StorageConfiguratorProps};
export {storageConfigurators, isLocalStorage, isSftpStorage, getStorageConfigurator};
