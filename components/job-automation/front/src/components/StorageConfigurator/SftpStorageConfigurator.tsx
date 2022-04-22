import React from 'react';
import {TextField, useTranslate} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isSftpStorage} from './model';

const SftpStorageConfigurator = ({storage, onChange}: StorageConfiguratorProps) => {
  if (!isSftpStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for sftp storage configurator`);
  }

  const translate = useTranslate();

  return (
    <>
      <TextField
        value={storage.filePath}
        label={translate('akeneo.automation.storage.file_path.label')}
        onChange={filePath => onChange({...storage, filePath})}
      />
      <TextField
        value={storage.host}
        label={translate('akeneo.automation.storage.host.label')}
        onChange={host => onChange({...storage, host})}
      />
      <TextField
        value={storage.username}
        label={translate('akeneo.automation.storage.username.label')}
        onChange={(username: string) => onChange({...storage, username})}
      />
      <TextField
        value={storage.password}
        type="password"
        label={translate('akeneo.automation.storage.password.label')}
        onChange={(password: string) => onChange({...storage, password})}
      />
    </>
  );
};

export {SftpStorageConfigurator};
