import {FeatureFlags} from '@akeneo-pim-community/shared';
import {getStorageConfigurator} from './model';
import {LocalStorageConfigurator} from './LocalStorageConfigurator';
import {SftpStorageConfigurator} from './SftpStorageConfigurator';
import {AmazonS3StorageConfigurator} from './AmazonS3StorageConfigurator';
import {GoogleCloudStorageConfigurator} from './GoogleCloudStorageConfigurator';
import {MicrosoftAzureStorageConfigurator} from './MicrosoftAzureStorageConfigurator';

const featureFlagCollection = {
  import_export_local_storage: false,
  import_export_additional_storage: false,
};

const enableFeatureFlag = (featureFlag: string) => (featureFlagCollection[featureFlag] = true);

const featureFlags: FeatureFlags = {
  isEnabled: (featureFlag: string) => featureFlagCollection[featureFlag],
};

test('it returns storage configurator', () => {
  expect(getStorageConfigurator('none', featureFlags)).toBe(null);
  expect(getStorageConfigurator('local', featureFlags)).toBe(null);

  enableFeatureFlag('import_export_local_storage');
  expect(getStorageConfigurator('local', featureFlags)).toBe(LocalStorageConfigurator);
  expect(getStorageConfigurator('sftp', featureFlags)).toBe(null);
  expect(getStorageConfigurator('amazon_s3', featureFlags)).toBe(null);
  expect(getStorageConfigurator('microsoft_azure', featureFlags)).toBe(null);
  expect(getStorageConfigurator('google_cloud_storage', featureFlags)).toBe(null);

  enableFeatureFlag('import_export_additional_storage');
  expect(getStorageConfigurator('sftp', featureFlags)).toBe(SftpStorageConfigurator);
  expect(getStorageConfigurator('amazon_s3', featureFlags)).toBe(AmazonS3StorageConfigurator);
  expect(getStorageConfigurator('microsoft_azure', featureFlags)).toBe(MicrosoftAzureStorageConfigurator);
  expect(getStorageConfigurator('google_cloud_storage', featureFlags)).toBe(GoogleCloudStorageConfigurator);

  // @ts-expect-error - there is no storage configurator for type 'unknown'
  expect(getStorageConfigurator('unknown', featureFlags)).toBe(null);
});
