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
        label={translate('akeneo.job_automation.storage.file_path.label')}
        onChange={(file_path: string) => onStorageChange({...storage, file_path})}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
      <TextField
        required={true}
        value={storage.host}
        label={translate('akeneo.job_automation.storage.host.label')}
        onChange={(host: string) => onStorageChange({...storage, host})}
        errors={filterErrors(validationErrors, '[host]')}
      />
      <Field
        label={`${translate('akeneo.job_automation.storage.port.label')} ${translate('pim_common.required_label')}`}
      >
        <NumberInput
          min={1}
          max={65535}
          onChange={(port: string) => onStorageChange({...storage, port: parseInt(port, 10)})}
          invalid={0 < portValidationErrors.length}
          value={storage.port.toString()}
        />
        {portValidationErrors.map((error: ValidationError, key: number) => (
          <Helper key={key} level="error" inline={true}>
            {translate(error.messageTemplate, error.parameters, error.plural)}
          </Helper>
        ))}
      </Field>
      <TextField
        value={storage.username}
        required={true}
        label={translate('akeneo.job_automation.storage.username.label')}
        onChange={(username: string) => onStorageChange({...storage, username})}
        errors={filterErrors(validationErrors, '[username]')}
      />
      <TextField
        value={storage.password}
        required={true}
        type="password"
        label={translate('akeneo.job_automation.storage.password.label')}
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
            {translate('akeneo.automation.connection_checker.label')}
          </Button>
          {check ? <CheckIcon color={pimTheme.color.green100} /> : ''}
        </CheckStorageConnetion>
        <>
          {(undefined !== check && !check) ? (
            <Helper inline level="error">
              {translate('akeneo.job_automation.connection_checker.exception')}
            </Helper>
          ) : (
            ''
          )}
        </>
      </>
    </>
  );
};

export {SftpStorageConfigurator};
