import React from 'react';
import {Button} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps} from './model';
import {isAmazonS3Storage} from '../../models';
import {CheckStorageConnection} from './CheckStorageConnection';

const AmazonS3StorageConfigurator = ({
  jobInstanceCode,
  storage,
  fileExtension,
  validationErrors,
  onStorageChange,
}: StorageConfiguratorProps) => {
  if (!isAmazonS3Storage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for amazon s3 storage configurator`);
  }

  const translate = useTranslate();
  const secretIsStoredOnServer = storage.secret === undefined;

  return (
    <>
      <TextField
        required={true}
        value={storage.file_path}
        label={translate('pim_import_export.form.job_instance.storage_form.file_path.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.file_path.placeholder', {
          file_extension: fileExtension,
        })}
        onChange={file_path => onStorageChange({...storage, file_path})}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
      <TextField
        required={true}
        value={storage.region}
        label={translate('pim_import_export.form.job_instance.storage_form.region.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.region.placeholder')}
        onChange={(region: string) => onStorageChange({...storage, region: region})}
        errors={filterErrors(validationErrors, '[region]')}
      />
      <TextField
        required={true}
        value={storage.bucket}
        label={translate('pim_import_export.form.job_instance.storage_form.bucket.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.bucket.placeholder')}
        onChange={(bucket: string) => onStorageChange({...storage, bucket: bucket})}
        errors={filterErrors(validationErrors, '[bucket]')}
      />
      <TextField
        value={storage.key}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.key.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.key.placeholder')}
        onChange={(key: string) => onStorageChange({...storage, key})}
        errors={filterErrors(validationErrors, '[key]')}
      />
      <TextField
        actions={
          secretIsStoredOnServer && (
            <Button
              level="secondary"
              ghost={true}
              size="small"
              onClick={() => onStorageChange({...storage, secret: ''})}
            >
              {translate('pim_common.edit')}
            </Button>
          )
        }
        required={true}
        value={secretIsStoredOnServer ? '••••••••' : storage.secret}
        readOnly={secretIsStoredOnServer}
        type="password"
        label={translate('pim_import_export.form.job_instance.storage_form.secret.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.secret.placeholder')}
        onChange={(secret: string) => onStorageChange({...storage, secret})}
        errors={filterErrors(validationErrors, '[secret]')}
      />
      <CheckStorageConnection jobInstanceCode={jobInstanceCode} storage={storage} />
    </>
  );
};

export {AmazonS3StorageConfigurator};
