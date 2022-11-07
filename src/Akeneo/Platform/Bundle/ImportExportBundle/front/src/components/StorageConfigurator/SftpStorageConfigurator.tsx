import React from 'react';
import styled from 'styled-components';
import {Field, Helper, NumberInput, Button, CheckIcon, getColor, SelectInput} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isSftpStorage, StorageLoginType, STORAGE_LOGIN_TYPES} from './model';
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

const SftpStorageConfigurator = ({
  storage,
  fileExtension,
  validationErrors,
  onStorageChange,
}: StorageConfiguratorProps) => {
  if (!isSftpStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for sftp storage configurator`);
  }

  const translate = useTranslate();
  const portValidationErrors = filterErrors(validationErrors, '[port]');
  const [isValid, canCheckConnection, checkReliability] = useCheckStorageConnection(storage);

    const handleLoginTypeChange = (storageLoginType: string) => {
        onStorageChange({...storage, login_type: storageLoginType as StorageLoginType});
    }
    const handleFilePathChange = (filePath: string) => {
        onStorageChange({...storage, file_path: filePath});
    }
    const handleHostChange = (host: string) => {
        onStorageChange({...storage, host: host});
    }
    const handlePortChange = (port: string) => {
        onStorageChange({...storage, port: parseInt(port, 10)});
    }

  return (
    <>
      <TextField
        required={true}
        value={storage.file_path}
        label={translate('pim_import_export.form.job_instance.storage_form.file_path.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.file_path.placeholder', {
          file_extension: fileExtension,
        })}
        onChange={handleFilePathChange}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
      <TextField
        required={true}
        value={storage.host}
        label={translate('pim_import_export.form.job_instance.storage_form.host.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.host.placeholder')}
        onChange={host => onStorageChange({...storage, host})}
        errors={filterErrors(validationErrors, '[host]')}
      />
      <TextField
        required={false}
        value={storage.fingerprint ?? ''}
        label={translate('pim_import_export.form.job_instance.storage_form.fingerprint.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.fingerprint.placeholder')}
        onChange={fingerprint =>
          onStorageChange({...storage, fingerprint: '' === fingerprint ? undefined : fingerprint})
        }
        errors={filterErrors(validationErrors, '[fingerprint]')}
      >
        <Helper>{translate('pim_import_export.form.job_instance.storage_form.fingerprint.helper')}</Helper>
      </TextField>
      <Field
        label={`${translate('pim_import_export.form.job_instance.storage_form.port.label')} ${translate(
          'pim_common.required_label'
        )}`}
      >
        <NumberInput
          min={1}
          max={65535}
          onChange={handlePortChange}
          invalid={0 < portValidationErrors.length}
          value={storage.port.toString()}
          placeholder={translate('pim_import_export.form.job_instance.storage_form.port.placeholder')}
        />
        {portValidationErrors.map((error, key) => (
          <Helper key={key} level="error" inline={true}>
            {translate(error.messageTemplate, error.parameters, error.plural)}
          </Helper>
        ))}
      </Field>

      <Field label={translate('pim_import_export.form.job_instance.storage_form.login_type.label')}>
        <SelectInput
          value={storage.login_type}
          onChange={handleLoginTypeChange}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          clearable={false}
        >
          {STORAGE_LOGIN_TYPES.map(loginType => (
            <SelectInput.Option value={loginType} key={loginType}>
              {translate(`pim_import_export.form.job_instance.storage_form.connection_type.${loginType}`)}
            </SelectInput.Option>
          ))}
        </SelectInput>
        {filterErrors(validationErrors, '[login_type]').map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>

      <TextField
        value={storage.username}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.username.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.username.placeholder')}
        onChange={(username: string) => onStorageChange({...storage, username})}
        errors={filterErrors(validationErrors, '[username]')}
      />

      {storage.login_type === 'password' ? (
        <TextField
          value={storage.password ?? ''}
          required={true}
          type="password"
          label={translate('pim_import_export.form.job_instance.storage_form.password.label')}
          onChange={(password: string) => onStorageChange({...storage, password})}
          errors={filterErrors(validationErrors, '[password]')}
        />
      ) : (
        <TextField
          value={storage.public_key ?? ''}
          readOnly
          label={translate('pim_import_export.form.job_instance.storage_form.public_key.label')}
        />
      )}

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

export type {StorageLoginType};

export {SftpStorageConfigurator};
