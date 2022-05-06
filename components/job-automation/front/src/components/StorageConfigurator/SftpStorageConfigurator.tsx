import React from 'react';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isSftpStorage} from './model';

const SftpStorageConfigurator = ({storage, validationErrors, onStorageChange}: StorageConfiguratorProps) => {
  if (!isSftpStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for sftp storage configurator`);
  }

  const translate = useTranslate();

  return (
    <>
      <TextField
        value={storage.file_path}
        label={translate('akeneo.automation.storage.file_path.label')}
        onChange={file_path => onStorageChange({...storage, file_path})}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
      <TextField
        value={storage.host}
        label={translate('akeneo.automation.storage.host.label')}
        onChange={host => onStorageChange({...storage, host})}
        errors={filterErrors(validationErrors, '[host]')}
      />
      <TextField
        value={storage.port.toString()}
        label={translate('akeneo.automation.storage.port.label')}
        onChange={port => onStorageChange({...storage, port: parseInt(port, 10)})}
        errors={filterErrors(validationErrors, '[port]')}
      />
      <TextField
        value={storage.username}
        label={translate('akeneo.automation.storage.username.label')}
        onChange={(username: string) => onStorageChange({...storage, username})}
        errors={filterErrors(validationErrors, '[username]')}
      />
      <TextField
        value={storage.password}
        type="password"
        label={translate('akeneo.automation.storage.password.label')}
        onChange={(password: string) => onStorageChange({...storage, password})}
        errors={filterErrors(validationErrors, '[password]')}
      />
    </>
  );
};

export {SftpStorageConfigurator};
