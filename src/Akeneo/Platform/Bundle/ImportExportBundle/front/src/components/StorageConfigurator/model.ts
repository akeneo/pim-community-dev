import {FunctionComponent} from 'react';
import {ValidationError, FeatureFlags} from '@akeneo-pim-community/shared';
import {LocalStorage, SftpStorage, Storage, StorageType, localStorageIsEnabled} from '../model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';

type StorageLoginType = 'password' | 'private_key';

const STORAGE_LOGIN_TYPES = ['password', 'private_key'];

type StorageConfiguratorProps = {
  storage: Storage;
  onStorageChange: (storage: Storage) => void;
  validationErrors: ValidationError[];
};

type StorageConfiguratorCollection = {
  [storageType: string]: FunctionComponent<StorageConfiguratorProps> | null;
};

const STORAGE_CONFIGURATORS: StorageConfiguratorCollection = {
  none: null,
  sftp: SftpStorageConfigurator,
};

const getEnabledStorageConfigurators = (featureFlags: FeatureFlags): StorageConfiguratorCollection => {
  const enabledStorageConfigurators = {...STORAGE_CONFIGURATORS};

  if (localStorageIsEnabled(featureFlags)) {
    enabledStorageConfigurators['local'] = LocalStorageConfigurator;
  }

  return enabledStorageConfigurators;
};

const getStorageConfigurator = (
  storageType: StorageType,
  featureFlags: FeatureFlags
): FunctionComponent<StorageConfiguratorProps> | null => {
  const enabledStorageConfigurators = getEnabledStorageConfigurators(featureFlags);

  return enabledStorageConfigurators[storageType] ?? null;
};

const isLocalStorage = (storage: Storage): storage is LocalStorage =>
  'local' === storage.type && 'file_path' in storage;

const isSftpStorage = (storage: Storage): storage is SftpStorage => {
  return (
    'sftp' === storage.type &&
    'file_path' in storage &&
    'host' in storage &&
    'port' in storage &&
    'username' in storage &&
    'password' in storage &&
    'login_type' in storage
  );
};

export type {StorageConfiguratorProps, StorageLoginType};
export {isLocalStorage, isSftpStorage, getStorageConfigurator, STORAGE_LOGIN_TYPES};
