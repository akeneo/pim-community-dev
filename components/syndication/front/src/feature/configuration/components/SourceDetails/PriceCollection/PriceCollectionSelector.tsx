import React, {useState} from 'react';
import {Collapse, Field, Helper, Pill, SelectInput} from 'akeneo-design-system';
import {
  Section,
  filterErrors,
  useTranslate,
  getAllLocalesFromChannels,
  ValidationError,
  ChannelReference,
} from '@akeneo-pim-community/shared';
import {
  PriceCollectionSelection,
  availableSeparators,
  isPriceCollectionSeparator,
  isDefaultPriceCollectionSelection,
} from './model';
import {LocaleDropdown} from '../../shared/LocaleDropdown';
import {useChannels} from '../../../hooks';
import {CurrenciesSelector} from './CurrenciesSelector';

type PriceCollectionSelectorProps = {
  channelReference: ChannelReference;
  selection: PriceCollectionSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: PriceCollectionSelection) => void;
};

const PriceCollectionSelector = ({
  channelReference,
  selection,
  validationErrors,
  onSelectionChange,
}: PriceCollectionSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const typeErrors = filterErrors(validationErrors, '[type]');
  const separatorErrors = filterErrors(validationErrors, '[separator]');
  const currenciesErrors = filterErrors(validationErrors, '[currencies]');

  if ('price' === selection.type) {
    throw new Error('PriceCollectionSelector can only be used with price_collection type');
  }

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.syndication.data_mapping_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultPriceCollectionSelection(selection) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
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
              title={translate('akeneo.syndication.data_mapping_details.sources.selection.type.amount')}
              value="amount"
            >
              {translate('akeneo.syndication.data_mapping_details.sources.selection.type.amount')}
            </SelectInput.Option>
            <SelectInput.Option
              title={translate('akeneo.syndication.data_mapping_details.sources.selection.price.currency_code')}
              value="currency_code"
            >
              {translate('akeneo.syndication.data_mapping_details.sources.selection.price.currency_code')}
            </SelectInput.Option>
            <SelectInput.Option
              title={translate('akeneo.syndication.data_mapping_details.sources.selection.price.currency_label')}
              value="currency_label"
            >
              {translate('akeneo.syndication.data_mapping_details.sources.selection.price.currency_label')}
            </SelectInput.Option>
          </SelectInput>
          <Helper inline={true} level="info">
            {translate('akeneo.syndication.data_mapping_details.sources.selection.price.information')}
          </Helper>
          {typeErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
        {'currency_label' === selection.type && (
          <LocaleDropdown
            label={translate('akeneo.syndication.data_mapping_details.sources.selection.price.currency_locale')}
            value={selection.locale}
            validationErrors={localeErrors}
            locales={locales}
            onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
          />
        )}
        <CurrenciesSelector
          value={selection.currencies ?? []}
          onChange={updatedValue => onSelectionChange({...selection, currencies: updatedValue})}
          channelReference={channelReference}
          validationErrors={currenciesErrors}
        />
        <Field
          label={translate('akeneo.syndication.data_mapping_details.sources.selection.collection_separator.title')}
        >
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
            {Object.entries(availableSeparators).map(([separator, name]) => (
              <SelectInput.Option
                key={separator}
                title={translate(
                  `akeneo.syndication.data_mapping_details.sources.selection.collection_separator.${name}`
                )}
                value={separator}
              >
                {translate(`akeneo.syndication.data_mapping_details.sources.selection.collection_separator.${name}`)}
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
    </Collapse>
  );
};

export {PriceCollectionSelector};
