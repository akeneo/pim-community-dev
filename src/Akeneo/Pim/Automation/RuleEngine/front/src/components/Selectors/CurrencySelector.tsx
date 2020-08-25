import React from 'react';
import { Currency, CurrencyCode } from '../../models/Currency';
import { Select2SimpleSyncWrapper, Select2Value } from '../Select2Wrapper';
import { useTranslate } from '../../dependenciesTools/hooks';
import {
  Attribute,
  AttributeType,
  getAttributeLabel,
  LocaleCode,
  ScopeCode,
} from '../../models';
import { Translate } from '../../dependenciesTools';
import { IndexedCurrencies } from '../../repositories/CurrencyRepository';

const getCurrencyValidation = (
  attribute: Attribute,
  translate: Translate,
  currentCatalogLocale: LocaleCode,
  availableCurrencies: Currency[],
  currencies: IndexedCurrencies,
  channelCode: ScopeCode,
  isCurrencyRequired = true
) => {
  const currencyValidation: any = {};

  if (isCurrencyRequired && attribute.type === AttributeType.PRICE_COLLECTION) {
    currencyValidation['required'] = translate(
      'pimee_catalog_rule.exceptions.required_currency',
      {
        attributeLabel: getAttributeLabel(attribute, currentCatalogLocale),
      }
    );
  }
  currencyValidation['validate'] = (selectedCode: CurrencyCode) => {
    if (!selectedCode || selectedCode === '') {
      return;
    }
    if ('undefined' === typeof currencies[selectedCode]) {
      return translate(
        'pimee_catalog_rule.exceptions.unknown_or_inactive_currency',
        { currencyCode: selectedCode }
      );
    }
    if (!availableCurrencies.some(currency => currency.code === selectedCode)) {
      return attribute.scopable
        ? translate('pimee_catalog_rule.exceptions.unbound_currency', {
            currencyCode: selectedCode,
            channelCode,
          })
        : translate(
            'pimee_catalog_rule.exceptions.unknown_or_inactive_currency',
            { currencyCode: selectedCode }
          );
    }
    return true;
  };

  return currencyValidation;
};

type Props = {
  label?: string;
  hiddenLabel?: boolean;
  availableCurrencies: Currency[];
  value?: CurrencyCode;
  onChange?: (value: CurrencyCode) => void;
  allowClear?: boolean;
  disabled?: boolean;
  validation?: { required?: string; validate?: (value: any) => string | true };
};

const CurrencySelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  availableCurrencies,
  value,
  onChange,
  allowClear = false,
  disabled = false,
  validation,
  ...remainingProps
}) => {
  const translate = useTranslate();

  const currencyChoices = availableCurrencies.map((currency: Currency) => {
    return {
      id: currency.code,
      text: currency.code,
    };
  });

  const handleChange = (value: Select2Value) => {
    if (onChange) {
      onChange(value as CurrencyCode);
    }
  };

  return (
    <Select2SimpleSyncWrapper
      {...remainingProps}
      label={
        label ||
        `${translate(
          'pimee_catalog_rule.form.edit.fields.currency'
        )} ${translate('pim_common.required_label')}`
      }
      hiddenLabel={hiddenLabel}
      data={currencyChoices}
      hideSearch={true}
      placeholder={translate('pimee_catalog_rule.form.edit.fields.currency')}
      value={value || null}
      onChange={handleChange}
      allowClear={allowClear}
      disabled={disabled}
      validation={validation}
      dropdownCssClass='currency-dropdown'
    />
  );
};

export { CurrencySelector, getCurrencyValidation };
