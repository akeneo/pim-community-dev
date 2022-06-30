import React from 'react';
import {Field, Helper, NumberInput} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isSftpStorage} from './model';
import {StorageConnectionChecker} from "./StorageConnectionChecker";

const SftpStorageConfigurator = ({storage, validationErrors, onStorageChange}: StorageConfiguratorProps) => {
  if (!isSftpStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for sftp storage configurator`);
  }

  const translate = useTranslate();
  const portValidationErrors = filterErrors(validationErrors, '[port]');

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
        onChange={host => onStorageChange({...storage, host})}
        errors={filterErrors(validationErrors, '[host]')}
      />
      <Field
        label={`${translate('akeneo.job_automation.storage.port.label')} ${translate('pim_common.required_label')}`}
      >
        <NumberInput
          min={1}
          max={65535}
          onChange={port => onStorageChange({...storage, port: parseInt(port, 10)})}
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
      <StorageConnectionChecker storage={storage}/>
    </>
  );
};

export {SftpStorageConfigurator};
