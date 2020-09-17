import React from 'react';
import { InputValueProps } from './AttributeValue';
import { CurrenciesSelector } from '../../../../../components/Selectors/CurrenciesSelector';
import { getScopeByCode } from '../../../../../repositories/ScopeRepository';
import { getAllCurrencies } from '../../../../../repositories/CurrencyRepository';
import { useBackboneRouter } from '../../../../../dependenciesTools/hooks';
import { CurrencyCode } from '../../../../../models/Currency';

const RemoveCurrencyFromPriceCollectionValue: React.FC<InputValueProps> = ({
  id,
  value,
  name,
  scopeCode,
  onChange,
}) => {
  const router = useBackboneRouter();
  const [availableCurrencyCodes, setAvailableCurrencyCodes] = React.useState<
    CurrencyCode[]
  >([]);

  const handleChange = (value: CurrencyCode[]) => {
    if (onChange) {
      onChange(
        value
          .filter(currencyCode => availableCurrencyCodes.includes(currencyCode))
          .map(currencyCode => {
            return { amount: 0, currency: currencyCode };
          })
      );
    }
  };

  const getSelectedCurrencyCodes = (): CurrencyCode[] =>
    Array.isArray(value)
      ? value
          .filter(
            price =>
              typeof price === 'object' &&
              price !== null &&
              Object.hasOwnProperty.call(price, 'currency')
          )
          .map(price => (price.currency ?? '') as CurrencyCode)
      : [];

  React.useEffect(() => {
    if (scopeCode) {
      getScopeByCode(scopeCode, router).then(scope =>
        setAvailableCurrencyCodes(scope?.currencies ?? [])
      );
    } else {
      getAllCurrencies(router).then(currencies =>
        setAvailableCurrencyCodes(
          Object.values(currencies).map(currency => currency.code)
        )
      );
    }
  }, [scopeCode]);

  React.useEffect(() => handleChange(getSelectedCurrencyCodes()), [
    availableCurrencyCodes,
  ]);

  return (
    <CurrenciesSelector
      data-testid={id}
      value={getSelectedCurrencyCodes()}
      name={name}
      availableCurrencyCodes={availableCurrencyCodes}
      onChange={handleChange}
    />
  );
};

export { RemoveCurrencyFromPriceCollectionValue };
