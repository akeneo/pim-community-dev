import React from 'react';
import {Field, Helper, NumberInput} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isSftpStorage} from './model';

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
        label={translate('pim_import_export.form.job_instance.storage_form.file_path.label')}
        onChange={file_path => onStorageChange({...storage, file_path})}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
      <TextField
        required={true}
        value={storage.host}
        label={translate('pim_import_export.form.job_instance.storage_form.host.label')}
        onChange={host => onStorageChange({...storage, host})}
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
    </>
  );
};

export {SftpStorageConfigurator};
