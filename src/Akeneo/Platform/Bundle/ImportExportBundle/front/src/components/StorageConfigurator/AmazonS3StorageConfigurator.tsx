import React from 'react';
import styled from 'styled-components';
import {Helper, Button, CheckIcon, getColor} from 'akeneo-design-system';
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

const AmazonS3StorageConfigurator = ({
  storage,
  fileExtension,
  validationErrors,
  onStorageChange,
}: StorageConfiguratorProps) => {
  if (!isAmazonS3Storage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for amazon s3 storage configurator`);
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
        required={true}
        value={storage.secret === undefined ? '****' : storage.secret}
        readOnly={storage.secret === undefined}
        type="password"
        label={translate('pim_import_export.form.job_instance.storage_form.secret.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.secret.placeholder')}
        onChange={(secret: string) => storage.secret !== undefined && onStorageChange({...storage, secret})}
        errors={filterErrors(validationErrors, '[secret]')}
      >
        {storage.secret === undefined && (
          <Button level="primary" onClick={() => {onStorageChange({...storage, secret: ''})}}>
            Edit password
          </Button>
        )}
      </TextField>
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
