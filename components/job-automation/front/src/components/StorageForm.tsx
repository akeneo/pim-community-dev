import React from 'react';
import {SectionTitle, Field, SelectInput} from 'akeneo-design-system';
import {Section, useTranslate} from '@akeneo-pim-community/shared';
import {Storage, isValidStorageType, getDefaultStorage, STORAGE_TYPES} from './model';
import {getStorageConfigurator} from './StorageConfigurator';

type StorageFormProps = {
  storage: Storage;
  onChange: (storage: Storage) => void;
};

const StorageForm = ({storage, onChange}: StorageFormProps) => {
  const translate = useTranslate();

  const handleTypeChange = (type: string) => isValidStorageType(type) && onChange(getDefaultStorage(type));

  const StorageConfigurator = getStorageConfigurator(storage.type);

  return (
    <Section>
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo.automation.storage.title')}</SectionTitle.Title>
      </SectionTitle>
      <Field label={translate('akeneo.automation.storage.connection.label')}>
        <SelectInput
          value={storage.type}
          onChange={handleTypeChange}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          clearable={false}
        >
          {STORAGE_TYPES.map(storageType => (
            <SelectInput.Option value={storageType} key={storageType}>
              {translate(`akeneo.automation.storage.connection.${storageType}`)}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </Field>
      {null !== StorageConfigurator && <StorageConfigurator storage={storage} onChange={onChange} />}
    </Section>
  );
};

export type {StorageFormProps};

export {StorageForm};
