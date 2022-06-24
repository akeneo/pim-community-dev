import React, {useState} from 'react';
import {Field, Helper, NumberInput, Button, CheckIcon, pimTheme} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isSftpStorage, ConnectionCheck} from './model';
import {LocalStorage, NoneStorage, SftpStorage} from '../model';
import {useRoute} from '@akeneo-pim-community/shared/lib/hooks/useRoute';
import styled from 'styled-components';

const Wrapper = styled.div`
  display: flex;
  align-items: center;
  gap: 8.5px;
`;
const SftpStorageConfigurator = ({storage, validationErrors, onStorageChange}: StorageConfiguratorProps) => {
  if (!isSftpStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for sftp storage configurator`);
  }
  const translate = useTranslate();
  const route = useRoute('pimee_job_automation_get_storage_connection_check');
  const portValidationErrors = filterErrors(validationErrors, '[port]');
  const [check, setCheck] = useState<ConnectionCheck>();
  const [isChecking, setIsChecking] = useState<boolean>(false);

  const checkData = async (storage: LocalStorage | SftpStorage | NoneStorage) => {
    setIsChecking(true);
    const response = await fetch(route, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(storage),
    });

    if (response.ok) {
      const data: ConnectionCheck = await response.json();
      setCheck(data);
    }
    setIsChecking(false);
  };

  return (
    <>
      <TextField
        required={true}
        value={storage.file_path}
        label={translate('akeneo.job_automation.storage.file_path.label')}
        onChange={file_path => onStorageChange({...storage, file_path})}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
      <TextField
        required={true}
        value={storage.host}
        label={translate('akeneo.job_automation.storage.host.label')}
        onChange={host => {
          onStorageChange({...storage, host});
          setCheck(undefined);
        }}
        errors={filterErrors(validationErrors, '[host]')}
      />
      <Field
        label={`${translate('akeneo.job_automation.storage.port.label')} ${translate('pim_common.required_label')}`}
      >
        <NumberInput
          min={1}
          max={65535}
          onChange={port => {
            onStorageChange({...storage, port: parseInt(port, 10)});
            setCheck(undefined);
          }}
          invalid={0 < portValidationErrors.length}
          value={storage.port.toString()}
        />
        {portValidationErrors.map((error, key) => (
          <Helper key={key} level="error" inline={true}>
            {translate(error.messageTemplate, error.parameters, error.plural)}
          </Helper>
        ))}
      </Field>
      <TextField
        value={storage.username}
        required={true}
        label={translate('akeneo.job_automation.storage.username.label')}
        onChange={(username: string) => {
          onStorageChange({...storage, username});
          setCheck(undefined);
        }}
        errors={filterErrors(validationErrors, '[username]')}
      />
      <TextField
        value={storage.password}
        required={true}
        type="password"
        label={translate('akeneo.job_automation.storage.password.label')}
        onChange={(password: string) => {
          onStorageChange({...storage, password});
          setCheck(undefined);
        }}
        errors={filterErrors(validationErrors, '[password]')}
      />
      <>
        <Wrapper>
          <Button
            onClick={() => {
              checkData(storage);
            }}
            disabled={(check && check.is_connection_healthy) || isChecking}
            level="primary"
          >
            {translate('akeneo.automation.connection_checker.label')}
          </Button>
          {check && check.is_connection_healthy ? <CheckIcon color={pimTheme.color.green100} /> : ''}
        </Wrapper>
        <>
          {check && !check.is_connection_healthy ? (
            <Helper inline level="error">
              {check.error_message}
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
