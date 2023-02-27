import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {MultiSelectInput} from 'akeneo-design-system';
import {ProductValueFiltersValues} from '../models/ProductValueFiltersValues';
import {useCurrencies} from '../../../hooks/useCurrencies';

type Props = {
    productValueFilters: ProductValueFiltersValues;
    onChange: (values: ProductValueFiltersValues) => void;
    isInvalid: boolean;
};

export const FilterCurrencies: FC<Props> = ({productValueFilters, onChange, isInvalid}) => {
    const translate = useTranslate();
    const {data: currencies} = useCurrencies();

    return (
        <>
            <MultiSelectInput
                value={productValueFilters?.currencies ?? []}
                emptyResultLabel={translate('akeneo_catalogs.product_value_filters.filters.currency.no_matches')}
                openLabel={translate('akeneo_catalogs.product_value_filters.action.open')}
                removeLabel={translate('akeneo_catalogs.product_value_filters.action.remove')}
                placeholder={translate('akeneo_catalogs.product_value_filters.filters.currency.placeholder')}
                onChange={v => onChange({...productValueFilters, currencies: v})}
                invalid={isInvalid}
                data-testid='product-value-filter-by-currency'
            >
                {currencies?.map(currency => (
                    <MultiSelectInput.Option key={currency} title={currency} value={currency}>
                        {currency}
                    </MultiSelectInput.Option>
                ))}
            </MultiSelectInput>
        </>
    );
};
