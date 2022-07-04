import {FunctionComponent} from 'react';
import {ValidationError, FeatureFlags} from '@akeneo-pim-community/shared';
import {LocalStorage, SftpStorage, Storage, StorageType} from '../model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';

type StorageConfiguratorProps = {
  storage: Storage;
  onStorageChange: (storage: Storage) => void;
  validationErrors: ValidationError[];
};

//TODO: Use a more accurate type
type StorageConfiguratorCollection = {
  [storageType: string]: FunctionComponent<StorageConfiguratorProps> | null;
};

const STORAGE_CONFIGURATORS: StorageConfiguratorCollection = {
  none: null,
};

const getEnabledStorageConfigurators = (featureFlags: FeatureFlags): StorageConfiguratorCollection => {
  const enabledStorageConfigurators = {...STORAGE_CONFIGURATORS};

  if (featureFlags.isEnabled('job_automation_local_storage')) {
    enabledStorageConfigurators['local'] = LocalStorageConfigurator;
  }

  if (featureFlags.isEnabled('job_automation_remote_storage')) {
    enabledStorageConfigurators['sftp'] = SftpStorageConfigurator;
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
