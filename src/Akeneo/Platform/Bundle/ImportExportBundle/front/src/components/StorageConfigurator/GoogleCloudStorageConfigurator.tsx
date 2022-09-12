import React from 'react';
import styled from 'styled-components';
import {Helper, Button, CheckIcon, getColor, Field, TextAreaInput} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors, formatParameters} from '@akeneo-pim-community/shared';
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

const GoogleCloudStorageConfigurator = ({storage, validationErrors, onStorageChange}: StorageConfiguratorProps) => {
  if (!isGoogleCloudStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for Azure blob storage configurator`);
  }

  const translate = useTranslate();
  const [isValid, canCheckConnection, checkReliability] = useCheckStorageConnection(storage);

  return (
    <>
      <TextField
        required={true}
        value={storage.file_path}
        label={translate('pim_import_export.form.job_instance.storage_form.file_path.label')}
        onChange={(file_path: string) => onStorageChange({...storage, file_path})}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
      <TextField
        value={storage.project_id}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.project_id.label')}
        onChange={(project_id: string) => onStorageChange({...storage, project_id})}
        errors={filterErrors(validationErrors, '[connection_string]')}
      />
      <Field
        label={translate('pim_import_export.form.job_instance.storage_form.service_account.label')}
        requiredLabel={translate('pim_common.required_label')}
      >
        <TextAreaInput
          value={storage.service_account}
          required={true}
          onChange={(service_account: string) => onStorageChange({...storage, service_account})}
        />
        {formatParameters(filterErrors(validationErrors, '[service_account]')).map((error, key) => (
          <Helper key={key} level="error" inline={true}>
            {translate(error.messageTemplate, error.parameters, error.plural)}
          </Helper>
        ))}
      </Field>
      <TextField
        value={storage.bucket}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.bucket.label')}
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
