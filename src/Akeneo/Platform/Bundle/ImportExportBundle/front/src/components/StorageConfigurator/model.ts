import {FunctionComponent} from 'react';
import {ValidationError, FeatureFlags} from '@akeneo-pim-community/shared';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';
import {AmazonS3StorageConfigurator} from './AmazonS3StorageConfigurator';
import {MicrosoftAzureStorageConfigurator} from './MicrosoftAzureStorageConfigurator';
import {GoogleCloudStorageConfigurator} from './GoogleCloudStorageConfigurator';
import {localStorageIsEnabled, additionalStorageIsEnabled, StorageType, Storage} from '../../models';

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
};

const getEnabledStorageConfigurators = (featureFlags: FeatureFlags): StorageConfiguratorCollection => {
  const enabledStorageConfigurators = {...STORAGE_CONFIGURATORS};

  if (localStorageIsEnabled(featureFlags)) {
    enabledStorageConfigurators['local'] = LocalStorageConfigurator;
  }

  if (additionalStorageIsEnabled(featureFlags)) {
    enabledStorageConfigurators['sftp'] = SftpStorageConfigurator;
    enabledStorageConfigurators['amazon_s3'] = AmazonS3StorageConfigurator;
    enabledStorageConfigurators['microsoft_azure'] = MicrosoftAzureStorageConfigurator;
    enabledStorageConfigurators['google_cloud_storage'] = GoogleCloudStorageConfigurator;
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

export type {StorageConfiguratorProps};
export {getStorageConfigurator};
