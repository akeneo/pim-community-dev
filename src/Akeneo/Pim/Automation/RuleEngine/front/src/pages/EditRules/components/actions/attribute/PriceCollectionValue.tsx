import React from 'react';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import { InputValueProps } from './AttributeValue';
import { getAttributeLabel } from '../../../../../models';
import {
  getAllCurrencies,
  getCurrenciesByCode,
  IndexedCurrencies,
} from '../../../../../repositories/CurrencyRepository';
import { Label } from '../../../../../components/Labels';
import { CurrencyCode } from '../../../../../models/Currency';
import { InputNumberWithHelper } from '../../../../../components/Inputs/InputNumberWithHelper';
import { getScopeByCode } from '../../../../../repositories/ScopeRepository';

type PriceCollectionData = { amount: number; currency: CurrencyCode }[];

const parsePriceCollectionValue: (
  value: any
) => PriceCollectionData = value => {
  const result: PriceCollectionData = [];
  if (Array.isArray(value)) {
    value.forEach(price => {
      if (
        Object.prototype.hasOwnProperty.call(price, 'amount') &&
        Object.prototype.hasOwnProperty.call(price, 'currency')
      ) {
        result.push({ amount: price.amount, currency: price.currency });
      }
    });
  }

  return result;
};

const PriceCollectionValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  value,
  label,
  onChange,
  scopeCode,
}) => {
  const currentCatalogLocale = useUserCatalogLocale();
  const router = useBackboneRouter();
  const translate = useTranslate();
  const [currencyCodes, setCurrencyCodes] = React.useState<CurrencyCode[]>();

  const setCurrencyCodesAndFilterValue = (currencies: IndexedCurrencies) => {
    const currencyCodes = Object.values(currencies).map(
      currency => currency.code
    );
    setCurrencyCodes(currencyCodes);
    onChange(
      (value as PriceCollectionData).filter(price =>
        currencyCodes.includes(price.currency)
      )
    );
  };

  React.useEffect(() => {
    if (scopeCode) {
      getScopeByCode(scopeCode, router).then(scope => {
        if (scope) {
          getCurrenciesByCode(scope.currencies, router).then(currencies =>
            setCurrencyCodesAndFilterValue(currencies)
          );
        } else {
          getAllCurrencies(router).then(currencies =>
            setCurrencyCodesAndFilterValue(currencies)
          );
        }
      });
    } else {
      getAllCurrencies(router).then(currencies =>
        setCurrencyCodesAndFilterValue(currencies)
      );
    }
  }, [scopeCode]);

  const getValue = (currencyCode: CurrencyCode) => {
    const priceAmount = (value as PriceCollectionData).find(
      price => price.currency === currencyCode
    )?.amount;

    return typeof priceAmount !== 'undefined' ? priceAmount : '';
  };

  if (!currencyCodes) {
    return (
      <img
        src='/bundles/pimui/images//loader-V2.svg'
        alt={translate('pim_common.loading')}
      />
    );
  }

  const handleChange = (currency: CurrencyCode, amount: string) => {
    const newValue: PriceCollectionData = [...value];
    const index = newValue.findIndex(price => price.currency === currency);
    if (index >= 0) {
      if (amount !== '') {
        // Removes existing price
        newValue[index].amount = Number(amount);
      } else {
        // Update existing price
        newValue.splice(index, 1);
      }
    } else if (amount !== '') {
      // Add new price
      newValue.push({ amount: Number(amount), currency });
    }
    onChange(newValue);
  };

  return (
    <>
      <Label
        className='AknFieldContainer-label control-label'
        label={label || getAttributeLabel(attribute, currentCatalogLocale)}
      />
      <div className='AknPriceList'>
        {currencyCodes.map(currencyCode => {
          return (
            <div className='AknPriceList-item' key={currencyCode}>
              <InputNumberWithHelper
                data-testid={`${id}-${currencyCode}`}
                label={currencyCode}
                value={getValue(currencyCode)}
                onChange={(event: any) =>
                  handleChange(currencyCode, event.target.value)
                }
                hiddenLabel
                step={attribute?.decimals_allowed ? 0.01 : 1}
                helper={currencyCode}
              />
            </div>
          );
        })}
      </div>
    </>
  );
};

export { PriceCollectionValue, parsePriceCollectionValue };
