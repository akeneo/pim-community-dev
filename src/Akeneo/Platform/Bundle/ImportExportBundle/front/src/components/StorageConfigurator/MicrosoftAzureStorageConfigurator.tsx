import React from 'react';
import styled from 'styled-components';
import {Helper, Button, CheckIcon, getColor} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isMicrosoftAzureStorage} from './model';
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

const MicrosoftAzureStorageConfigurator = ({
  storage,
  fileExtension,
  validationErrors,
  onStorageChange,
}: StorageConfiguratorProps) => {
  if (!isMicrosoftAzureStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for microsoft azure storage configurator`);
  }

  const translate = useTranslate();
  const [isValid, canCheckConnection, checkReliability] = useCheckStorageConnection(storage);

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
        value={storage.connection_string}
        label={translate('pim_import_export.form.job_instance.storage_form.connection_string.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.connection_string.placeholder')}
        onChange={(connection_string: string) => onStorageChange({...storage, connection_string: connection_string})}
        errors={filterErrors(validationErrors, '[connection_string]')}
      />
      <TextField
        required={true}
        value={storage.container_name}
        label={translate('pim_import_export.form.job_instance.storage_form.container_name.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.container_name.placeholder')}
        onChange={(container_name: string) => onStorageChange({...storage, container_name: container_name})}
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

export {MicrosoftAzureStorageConfigurator};
