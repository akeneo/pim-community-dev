import React from 'react';
import styled from 'styled-components';
import {Helper, Button, CheckIcon, getColor} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isAzureBlobStorage} from './model';
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

const AzureBlobStorageConfigurator = ({storage, validationErrors, onStorageChange}: StorageConfiguratorProps) => {
  if (!isAzureBlobStorage(storage)) {
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
        value={storage.connection_string}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.connection_string.label')}
        onChange={(connection_string: string) => onStorageChange({...storage, connection_string})}
        errors={filterErrors(validationErrors, '[connection_string]')}
      />
      <TextField
        value={storage.container_name}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.container_name.label')}
        onChange={(container_name: string) => onStorageChange({...storage, container_name})}
        errors={filterErrors(validationErrors, '[container_name]')}
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

export {AzureBlobStorageConfigurator};
