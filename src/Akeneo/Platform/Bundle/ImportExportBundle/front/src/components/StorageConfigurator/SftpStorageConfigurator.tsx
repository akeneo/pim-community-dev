import React from 'react';
import {Field, Helper, NumberInput, Button, CheckIcon, pimTheme} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors, ValidationError} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isSftpStorage} from './model';
import styled from 'styled-components';
import {useCheckStorageConnection} from '../../hooks/useCheckStorageConnection';

const CheckStorageConnetion = styled.div`
  display: flex;
  align-items: center;
  gap: 8.5px;
`;

const SftpStorageConfigurator = ({storage, validationErrors, onStorageChange}: StorageConfiguratorProps) => {
  if (!isSftpStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for sftp storage configurator`);
  }

  const translate = useTranslate();
  const portValidationErrors = filterErrors(validationErrors, '[port]');
  const [check, isChecking, checkReliability] = useCheckStorageConnection(storage);

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
        onChange={(host: string) => onStorageChange({...storage, host})}
        errors={filterErrors(validationErrors, '[host]')}
      />
      <Field
        label={`${translate('pim_import_export.form.job_instance.storage_form.port.label')} ${translate(
          'pim_common.required_label'
        )}`}
      >
        <NumberInput
          min={1}
          max={65535}
          onChange={(port: string) => onStorageChange({...storage, port: parseInt(port, 10)})}
          invalid={0 < portValidationErrors.length}
          value={storage.port.toString()}
        />
        {portValidationErrors.map((error: ValidationError, key) => (
          <Helper key={key} level="error" inline={true}>
            {translate(error.messageTemplate, error.parameters, error.plural)}
          </Helper>
        ))}
      </Field>
      <TextField
        value={storage.username}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.username.label')}
        onChange={(username: string) => onStorageChange({...storage, username})}
        errors={filterErrors(validationErrors, '[username]')}
      />
      <TextField
        value={storage.password}
        required={true}
        type="password"
        label={translate('pim_import_export.form.job_instance.storage_form.password.label')}
        onChange={(password: string) => onStorageChange({...storage, password})}
        errors={filterErrors(validationErrors, '[password]')}
      />
      <>
        <CheckStorageConnetion>
          <Button
            onClick={() => {
              checkReliability();
            }}
            disabled={check || isChecking}
            level="primary"
          >
            {translate('pim_import_export.form.job_instance.connection_checker.label')}
          </Button>
          {check ? <CheckIcon color={pimTheme.color.green100} /> : ''}
        </CheckStorageConnetion>
        <>
          {undefined !== check && !check && (
            <Helper inline level="error">
              {translate('pim_import_export.form.job_instance.connection_checker.exception')}
            </Helper>
          )}
        </>
      </>
    </>
  );
};

export {SftpStorageConfigurator};
