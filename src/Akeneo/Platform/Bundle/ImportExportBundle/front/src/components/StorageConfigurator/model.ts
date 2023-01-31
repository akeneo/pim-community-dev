import {FunctionComponent} from 'react';
import {ValidationError, FeatureFlags} from '@akeneo-pim-community/shared';
import {
  LocalStorage,
  SftpStorage,
  AmazonS3Storage,
  Storage,
  StorageType,
  localStorageIsEnabled,
  MicrosoftAzureStorage,
} from '../model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';
import {AmazonS3StorageConfigurator} from './AmazonS3StorageConfigurator';
import {MicrosoftAzureStorageConfigurator} from './MicrosoftAzureStorageConfigurator';

type StorageLoginType = 'password' | 'private_key';

const STORAGE_LOGIN_TYPES = ['password', 'private_key'];

type StorageConfiguratorProps = {
  jobInstanceCode: string;
  storage: Storage;
  fileExtension: string;
  onStorageChange: (storage: Storage) => void;
  validationErrors: ValidationError[];
};

type StorageConfiguratorCollection = {
  [storageType: string]: FunctionComponent<StorageConfiguratorProps> | null;
};

const STORAGE_CONFIGURATORS: StorageConfiguratorCollection = {
  none: null,
  sftp: SftpStorageConfigurator,
  amazon_s3: AmazonS3StorageConfigurator,
  microsoft_azure: MicrosoftAzureStorageConfigurator,
};

const getEnabledStorageConfigurators = (featureFlags: FeatureFlags): StorageConfiguratorCollection => {
  const enabledStorageConfigurators = {...STORAGE_CONFIGURATORS};

  if (localStorageIsEnabled(featureFlags)) {
    enabledStorageConfigurators['local'] = LocalStorageConfigurator;
  }

  return enabledStorageConfigurators;
};

const isValidLoginType = (loginType: string): loginType is StorageLoginType => {
  return STORAGE_LOGIN_TYPES.includes(loginType);
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
    'login_type' in storage &&
    isValidLoginType(storage.login_type)
  );
};

const isAmazonS3Storage = (storage: Storage): storage is AmazonS3Storage => {
  return (
    'amazon_s3' === storage.type &&
    'file_path' in storage &&
    'region' in storage &&
    'bucket' in storage &&
    'key' in storage
  );
};

const isMicrosoftAzureStorage = (storage: Storage): storage is MicrosoftAzureStorage => {
  return 'microsoft_azure' === storage.type && 'file_path' in storage && 'container_name' in storage;
};

export type {StorageConfiguratorProps, StorageLoginType};
export {
  isLocalStorage,
  isSftpStorage,
  isAmazonS3Storage,
  isMicrosoftAzureStorage,
  isValidLoginType,
  getStorageConfigurator,
  STORAGE_LOGIN_TYPES,
};
