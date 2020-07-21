import React from 'react';
import { useBackboneRouter, useTranslate, useUserCatalogLocale } from '../../../../../dependenciesTools/hooks';
import { InputValueProps } from './AttributeValue';
import { getAttributeLabel } from '../../../../../models';
import { getAllCurrencies, IndexedCurrencies } from "../../../../../repositories/CurrencyRepository";
import { Label } from "../../../../../components/Labels";
import { CurrencyCode } from "../../../../../models/Currency";
import { PriceValue } from "./PriceValue";

type PriceCollectionData = {amount: number; currency: CurrencyCode}[];

const parsePriceCollectionValue: (value: any) => PriceCollectionData = (value) => {
  const result: PriceCollectionData = [];
  if (Array.isArray(value)) {
    value.forEach((price) => {
      if (Object.prototype.hasOwnProperty.call(price, 'amount') && Object.prototype.hasOwnProperty.call(price, 'currency')) {
        result.push({ amount: price.amount, currency: price.currency });
      }
    });
  }

  return result;
}

const PriceCollectionValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  value,
  label,
  onChange,
}) => {
  const currentCatalogLocale = useUserCatalogLocale();
  const router = useBackboneRouter();
  const translate = useTranslate();
  const [ currencies, setCurrencies ] = React.useState<IndexedCurrencies>({});

  React.useEffect(() => {
    getAllCurrencies(router).then(currencies => setCurrencies(currencies));
  }, []);

  const getValue = (currencyCode: CurrencyCode) => (value as PriceCollectionData).find((price) => price.currency === currencyCode)?.amount;

  if (Object.keys(currencies).length === 0) {
    return <img
      src='/bundles/pimui/images//loader-V2.svg'
      alt={translate('pim_common.loading')}
    />;
  }

  const handleChange = (currency: CurrencyCode, amount: string) => {
    const newValue: PriceCollectionData = [...value];
    const index = newValue.findIndex((price) => price.currency === currency);
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
  }

  return <>
    <Label
      className='AknFieldContainer-label control-label'
      label={label || getAttributeLabel(attribute, currentCatalogLocale)}
    />
    <div className='AknPriceList'>
      {Object.values(currencies).map((currency) => {
        return <div className='AknPriceList-item' key={currency.code}>
          <PriceValue
            data-testid={`${id}-${currency.code}`}
            label={currency.code}
            defaultValue={getValue(currency.code)}
            onChange={(event: any) => handleChange(currency.code, event.target.value)}
            hiddenLabel
            currencyCode={currency.code}
          />
        </div>
        }
      )}
    </div>
  </>
};

export { PriceCollectionValue, parsePriceCollectionValue };
