import React from 'react';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps} from './model';
import {isLocalStorage} from '../../models';

const LocalStorageConfigurator = ({
  storage,
  fileExtension,
  validationErrors,
  onStorageChange,
}: StorageConfiguratorProps) => {
  if (!isLocalStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for local storage configurator`);
  }

  const translate = useTranslate();

  return (
    <>
      <TextField
        required={true}
        value={storage.file_path}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.file_path.placeholder', {
          file_extension: fileExtension,
        })}
        label={translate('pim_import_export.form.job_instance.storage_form.file_path.label')}
        onChange={file_path => onStorageChange({...storage, file_path})}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
    </>
  );
};

export {LocalStorageConfigurator};
