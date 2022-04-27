import React from 'react';
import {TextField, useTranslate} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isLocalStorage} from './model';

const LocalStorageConfigurator = ({storage, onChange}: StorageConfiguratorProps) => {
  if (!isLocalStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for local storage configurator`);
  }

  const translate = useTranslate();

  return (
    <>
      <TextField
        value={storage.filePath}
        label={translate('akeneo.automation.storage.file_path.label')}
        onChange={filePath => onChange({...storage, filePath})}
      />
    </>
  );
};

export {LocalStorageConfigurator};
