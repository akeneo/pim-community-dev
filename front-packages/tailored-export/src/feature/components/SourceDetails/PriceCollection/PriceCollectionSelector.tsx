import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {
  Section,
  filterErrors,
  useTranslate,
  getAllLocalesFromChannels,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {PriceCollectionSelection, availableSeparators, isPriceCollectionSeparator} from './model';
import {LocaleDropdown} from '../../LocaleDropdown';
import {useChannels} from '../../../hooks';

type PriceCollectionSelectorProps = {
  selection: PriceCollectionSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: PriceCollectionSelection) => void;
};

const PriceCollectionSelector = ({selection, validationErrors, onSelectionChange}: PriceCollectionSelectorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const typeErrors = filterErrors(validationErrors, '[type]');
  const separatorErrors = filterErrors(validationErrors, '[separator]');

  return (
    <Section>
      <Field label={translate('pim_common.type')}>
        <SelectInput
          clearable={false}
          invalid={0 < typeErrors.length}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.type}
          onChange={type => {
            if ('currency_label' === type) {
              onSelectionChange({...selection, type, locale: locales[0].code});
            } else if ('currency_code' === type || 'amount' === type) {
              onSelectionChange({...selection, type});
            }
          }}
        >
          <SelectInput.Option
            title={translate('akeneo.tailored_export.column_details.sources.selection.type.amount')}
            value="amount"
          >
            {translate('akeneo.tailored_export.column_details.sources.selection.type.amount')}
          </SelectInput.Option>
          <SelectInput.Option
            title={translate('akeneo.tailored_export.column_details.sources.selection.type.currency_code')}
            value="currency_code"
          >
            {translate('akeneo.tailored_export.column_details.sources.selection.type.currency_code')}
          </SelectInput.Option>
          <SelectInput.Option
            title={translate('akeneo.tailored_export.column_details.sources.selection.type.currency_label')}
            value="currency_label"
          >
            {translate('akeneo.tailored_export.column_details.sources.selection.type.currency_label')}
          </SelectInput.Option>
        </SelectInput>
        <Helper inline={true} level="info">
          {translate('akeneo.tailored_export.column_details.sources.selection.price.information')}
        </Helper>
        {typeErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
      {'currency_label' === selection.type && (
        <LocaleDropdown
          label={translate('akeneo.tailored_export.column_details.sources.selection.type.currency_locale')}
          value={selection.locale}
          validationErrors={localeErrors}
          locales={locales}
          onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
        />
      )}
      <Field label={translate('akeneo.tailored_export.column_details.sources.selection.collection_separator.title')}>
        <SelectInput
          invalid={0 < separatorErrors.length}
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.separator}
          onChange={separator => {
            if (isPriceCollectionSeparator(separator)) {
              onSelectionChange({...selection, separator});
            }
          }}
        >
          {availableSeparators.map(availableSeparator => (
            <SelectInput.Option key={availableSeparator} title={availableSeparator} value={availableSeparator}>
              {availableSeparator}
            </SelectInput.Option>
          ))}
        </SelectInput>
        {separatorErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
    </Section>
  );
};

export {PriceCollectionSelector};
