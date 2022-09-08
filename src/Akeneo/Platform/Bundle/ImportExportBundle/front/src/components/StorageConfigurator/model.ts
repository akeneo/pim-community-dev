import {FunctionComponent} from 'react';
import {ValidationError, FeatureFlags} from '@akeneo-pim-community/shared';
import {
  LocalStorage,
  SftpStorage,
  Storage,
  StorageType,
  localStorageIsEnabled,
  AmazonS3Storage,
  AzureBlobStorage,
} from '../model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';
import {AmazonS3StorageConfigurator} from './AmazonS3StorageConfigurator';
import {AzureBlobStorageConfigurator} from './AzureBlobStorageConfigurator';

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
  sftp: SftpStorageConfigurator,
};

const getEnabledStorageConfigurators = (featureFlags: FeatureFlags): StorageConfiguratorCollection => {
  const enabledStorageConfigurators = {...STORAGE_CONFIGURATORS};

  if (localStorageIsEnabled(featureFlags)) {
    enabledStorageConfigurators['local'] = LocalStorageConfigurator;
  }

  enabledStorageConfigurators['sftp'] = SftpStorageConfigurator;
  enabledStorageConfigurators['amazon_s3'] = AmazonS3StorageConfigurator;
  enabledStorageConfigurators['azure_blob'] = AzureBlobStorageConfigurator;

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
    'password' in storage
  );
};

const isAmazonS3Storage = (storage: Storage): storage is AmazonS3Storage => {
  return (
    'amazon_s3' === storage.type &&
    'file_path' in storage &&
    'region' in storage &&
    'bucket_name' in storage &&
    'key' in storage &&
    'secret' in storage
  );
};

const isAzureBlobStorage = (storage: Storage): storage is AzureBlobStorage => {
  return (
    'azure_blob' === storage.type &&
    'file_path' in storage &&
    'connection_string' in storage &&
    'container_name' in storage
  );
};

const isStorageFulfilled = (storage: Storage): boolean => {
  if (isLocalStorage(storage)) {
    return '' !== storage.file_path;
  }

  if (isSftpStorage(storage)) {
    return (
      '' !== storage.file_path &&
      '' !== storage.host &&
      !isNaN(storage.port) &&
      '' !== storage.username &&
      '' !== storage.password
    );
  }

  if (isAmazonS3Storage(storage)) {
    return (
      '' !== storage.file_path &&
      '' !== storage.region &&
      '' !== storage.bucket_name &&
      '' !== storage.key &&
      '' !== storage.secret
    );
  }

  if (isAzureBlobStorage(storage)) {
    return '' !== storage.file_path && '' !== storage.connection_string && '' !== storage.container_name;
  }

  return false;
};

export type {StorageConfiguratorProps};
export {
  isLocalStorage,
  isSftpStorage,
  isAmazonS3Storage,
  isAzureBlobStorage,
  isStorageFulfilled,
  getStorageConfigurator,
};
