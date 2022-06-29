import {FunctionComponent} from 'react';
import {ValidationError} from '@akeneo-pim-community/shared';
import {LocalStorage, SftpStorage, Storage, StorageType} from '../model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';

type StorageConfiguratorProps = {
  storage: Storage;
  onStorageChange: (storage: Storage) => void;
  validationErrors: ValidationError[];
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
  return 'local' === storage.type && 'file_path' in storage;
};

const isSftpStorage = (storage: Storage): storage is SftpStorage => {
  return (
    'sftp' === storage.type &&
    'file_path' in storage &&
    'host' in storage &&
    'port' in storage &&
    'username' in storage &&
    'password' in storage
  );
};

export type {StorageConfiguratorProps};
export {isLocalStorage, isSftpStorage, getStorageConfigurator};
