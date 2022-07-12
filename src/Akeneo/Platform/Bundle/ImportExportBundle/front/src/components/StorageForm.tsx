import React from 'react';
import {SectionTitle, Field, SelectInput, Helper} from 'akeneo-design-system';
import {Section, useTranslate, ValidationError, filterErrors, useFeatureFlags} from '@akeneo-pim-community/shared';
import {Storage, isValidStorageType, getDefaultStorage, JobType, getEnabledStorageTypes, shouldHideForm} from './model';
import {getStorageConfigurator} from './StorageConfigurator';

type StorageFormProps = {
  jobName: string;
  jobType: JobType;
  fileExtension: string;
  storage: Storage;
  validationErrors: ValidationError[];
  onStorageChange: (storage: Storage) => void;
};

const StorageForm = ({
  jobName,
  jobType,
  fileExtension,
  storage,
  validationErrors,
  onStorageChange,
}: StorageFormProps) => {
  const translate = useTranslate();
  const featureFlags = useFeatureFlags();

  if (shouldHideForm(featureFlags, jobName)) {
    return null;
  }

  const handleTypeChange = (type: string) =>
    isValidStorageType(type, featureFlags, jobName) && onStorageChange(getDefaultStorage(jobType, type, fileExtension));

  const storageTypes = getEnabledStorageTypes(featureFlags, jobName);
  const StorageConfigurator = getStorageConfigurator(storage.type, featureFlags, jobName);

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
          {storageTypes.map(storageType => (
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
