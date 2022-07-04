import React from 'react';
import {SectionTitle, Field, SelectInput, Helper} from 'akeneo-design-system';
import {Section, useTranslate, ValidationError, filterErrors} from '@akeneo-pim-community/shared';
import {Storage, isValidStorageType, getDefaultStorage, STORAGE_TYPES, JobType, NoneStorage} from './model';
import {getStorageConfigurator} from './StorageConfigurator';

type StorageFormProps = {
  jobType: JobType;
  fileExtension: string;
  storage: Storage;
  validationErrors: ValidationError[];
  onStorageChange: (storage: Storage) => void;
};

const StorageForm = ({jobType, fileExtension, storage, validationErrors, onStorageChange}: StorageFormProps) => {
  const translate = useTranslate();

  const handleTypeChange = (type: string) =>
    isValidStorageType(type) && onStorageChange(getDefaultStorage(jobType, type, fileExtension));

  const StorageConfigurator = getStorageConfigurator(storage.type);

  return (
    <Section>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_import_export.form.job_instance.storage_form.title')}</SectionTitle.Title>
      </SectionTitle>
      <Field label={translate('pim_import_export.form.job_instance.storage_form.connection.label')}>
        <SelectInput
          value={storage.type}
          onChange={handleTypeChange}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          clearable={false}
        >
          {STORAGE_TYPES.map(storageType => (
            <SelectInput.Option value={storageType} key={storageType}>
              {'none' === storageType
                ? translate(`pim_import_export.form.job_instance.storage_form.connection.${storageType}.${jobType}`)
                : translate(`pim_import_export.form.job_instance.storage_form.connection.${storageType}`)}
            </SelectInput.Option>
          ))}
        </SelectInput>
        {filterErrors(validationErrors, '[type]').map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
      {null !== StorageConfigurator && (
        <StorageConfigurator storage={storage} validationErrors={validationErrors} onStorageChange={onStorageChange} />
      )}
    </Section>
  );
};

export type {StorageFormProps};

export {StorageForm};
