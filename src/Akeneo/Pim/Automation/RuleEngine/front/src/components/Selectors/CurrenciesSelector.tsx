import React from 'react';
import { Select2MultiSyncWrapper, Select2Value } from '../Select2Wrapper';
import { CurrencyCode } from '../../models/Currency';
import { useTranslate } from '../../dependenciesTools/hooks';

type Props = {
  label?: string;
  hiddenLabel?: boolean;
  value: CurrencyCode[];
  onChange?: (value: CurrencyCode[]) => void;
  name: string;
  availableCurrencies: CurrencyCode[];
};

const CurrenciesSelector: React.FC<Props> = ({
  label,
  availableCurrencies,
  onChange,
  ...remainingProps
}) => {
  const translate = useTranslate();
  const handleChange = (value: Select2Value[]) => {
    if (onChange) {
      onChange(value as CurrencyCode[]);
    }
  };
  const currencyChoices = availableCurrencies.map(
    (currencyCode: CurrencyCode) => {
      return {
        id: currencyCode,
        text: currencyCode,
      };
    }
  );

  return (
    <Select2MultiSyncWrapper
      label={label ?? translate('pim_enrich.entity.currency.plural_label')}
      data={currencyChoices}
      onChange={handleChange}
      allowClear={true}
      placeholder=' '
      {...remainingProps}
    />
  );
};

export { CurrenciesSelector };
