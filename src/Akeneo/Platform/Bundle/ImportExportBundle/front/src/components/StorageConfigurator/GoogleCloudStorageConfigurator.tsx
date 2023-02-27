import React from 'react';
import {Button} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps} from './model';
import {isGoogleCloudStorage} from '../../models';
import {CheckStorageConnection} from './CheckStorageConnection';

const GoogleCloudStorageConfigurator = ({
  jobInstanceCode,
  storage,
  fileExtension,
  validationErrors,
  onStorageChange,
}: StorageConfiguratorProps) => {
  if (!isGoogleCloudStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for google cloud storage configurator`);
  }

  const translate = useTranslate();
  const serviceAccountIsStoredOnServer = storage.service_account === undefined;

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
        value={storage.project_id}
        label={translate('pim_import_export.form.job_instance.storage_form.project_id.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.project_id.placeholder')}
        onChange={(project_id: string) => onStorageChange({...storage, project_id})}
        errors={filterErrors(validationErrors, '[project_id]')}
      />
      <TextField
        actions={
          serviceAccountIsStoredOnServer && (
            <Button
              level="secondary"
              ghost={true}
              size="small"
              onClick={() => onStorageChange({...storage, service_account: ''})}
            >
              {translate('pim_common.edit')}
            </Button>
          )
        }
        required={true}
        value={serviceAccountIsStoredOnServer ? '••••••••' : storage.service_account}
        readOnly={serviceAccountIsStoredOnServer}
        type="password"
        label={translate('pim_import_export.form.job_instance.storage_form.service_account.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.service_account.placeholder')}
        onChange={(service_account: string) => onStorageChange({...storage, service_account})}
        errors={filterErrors(validationErrors, '[service_account]')}
      />
      <TextField
        required={true}
        value={storage.bucket}
        label={translate('pim_import_export.form.job_instance.storage_form.bucket.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.bucket.placeholder')}
        onChange={(bucket: string) => onStorageChange({...storage, bucket})}
        errors={filterErrors(validationErrors, '[bucket]')}
      />
      <CheckStorageConnection jobInstanceCode={jobInstanceCode} storage={storage} />
    </>
  );
};

export {GoogleCloudStorageConfigurator};
