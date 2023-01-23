import React from 'react';
import styled from 'styled-components';
import {Helper, Button, CheckIcon, getColor} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isGoogleCloudStorage} from './model';
import {useCheckStorageConnection} from '../../hooks/useCheckStorageConnection';

const CheckStorageForm = styled.div`
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

const CheckStorageConnection = styled.div`
  display: flex;
  align-items: center;
  gap: 8.5px;
  color: ${getColor('green', 100)};
`;

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
  const [isValid, canCheckConnection, checkReliability] = useCheckStorageConnection(jobInstanceCode, storage);

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
        required={true}
        value={storage.service_account}
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
      <CheckStorageForm>
        <CheckStorageConnection>
          <Button onClick={checkReliability} disabled={!canCheckConnection} level="primary">
            {translate('pim_import_export.form.job_instance.connection_checker.label')}
          </Button>
          {isValid && <CheckIcon />}
        </CheckStorageConnection>
        {false === isValid && (
          <Helper inline={true} level="error">
            {translate('pim_import_export.form.job_instance.connection_checker.exception')}
          </Helper>
        )}
      </CheckStorageForm>
    </>
  );
};

export {GoogleCloudStorageConfigurator};
