import React from 'react';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isLocalStorage} from './model';

const LocalStorageConfigurator = ({storage, validationErrors, onStorageChange}: StorageConfiguratorProps) => {
  if (!isLocalStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for local storage configurator`);
  }

  const translate = useTranslate();

  return (
    <>
      <TextField
        required={true}
        value={storage.file_path}
        label={translate('akeneo.automation.storage.file_path.label')}
        onChange={file_path => onStorageChange({...storage, file_path})}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
    </>
  );
};

export {LocalStorageConfigurator};
