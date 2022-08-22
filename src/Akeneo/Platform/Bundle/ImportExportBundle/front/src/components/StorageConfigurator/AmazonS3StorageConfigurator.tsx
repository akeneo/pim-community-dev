import React from 'react';
import styled from 'styled-components';
import {Field, Helper, NumberInput, Button, CheckIcon, getColor} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isAmazonS3Storage} from './model';
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

const AmazonS3StorageConfigurator = ({storage, validationErrors, onStorageChange}: StorageConfiguratorProps) => {
  if (!isAmazonS3Storage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for Amazon S3 storage configurator`);
  }

  const translate = useTranslate();
  const [isValid, canCheckConnection, checkReliability] = useCheckStorageConnection(storage);

  return (
    <>
      <TextField
        required={true}
        value={storage.file_path}
        label={translate('pim_import_export.form.job_instance.storage_form.file_path.label')}
        onChange={file_path => onStorageChange({...storage, file_path})}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
      <TextField
        required={true}
        value={storage.host}
        label={translate('pim_import_export.form.job_instance.storage_form.host.label')}
        onChange={host => onStorageChange({...storage, host})}
        errors={filterErrors(validationErrors, '[host]')}
      />
      <TextField
        value={storage.region}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.region.label')}
        onChange={(region: string) => onStorageChange({...storage, region})}
        errors={filterErrors(validationErrors, '[region]')}
      />
      <TextField
        value={storage.bucket_name}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.bucket_name.label')}
        onChange={(bucket_name: string) => onStorageChange({...storage, bucket_name})}
        errors={filterErrors(validationErrors, '[bucket_name]')}
      />
      <TextField
        value={storage.key}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.key.label')}
        onChange={(key: string) => onStorageChange({...storage, key})}
        errors={filterErrors(validationErrors, '[key]')}
      />
      <TextField
        value={storage.secret}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.secret.label')}
        onChange={(secret: string) => onStorageChange({...storage, secret})}
        errors={filterErrors(validationErrors, '[secret]')}
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

export {AmazonS3StorageConfigurator};
