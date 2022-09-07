import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {MultiSelectInput} from 'akeneo-design-system';
import {ProductValueFiltersValues} from '../models/ProductValueFiltersValues';
import {useUniqueEntitiesByCode} from '../../../hooks/useUniqueEntitiesByCode';
import {useLocalesByCodes} from '../hooks/useLocalesByCodes';
import {Locale} from '../models/Locale';
import {useInfiniteLocales} from '../hooks/useInfiniteLocales';

type Props = {
    productValueFilters: ProductValueFiltersValues;
    onChange: (values: ProductValueFiltersValues) => void;
    isInvalid: boolean;
};

export const FilterLocale: FC<Props> = ({productValueFilters, onChange, isInvalid}) => {
    const translate = useTranslate();

    const {data: selected} = useLocalesByCodes(productValueFilters?.locale);
    const {data: results, fetchNextPage} = useInfiniteLocales();
    const locales = useUniqueEntitiesByCode<Locale>(selected, results);

    return (
        <>
            <MultiSelectInput
                value={productValueFilters?.locale ?? []}
                emptyResultLabel={translate('akeneo_catalogs.product_value_filters.locale.no_matches')}
                openLabel={translate('akeneo_catalogs.product_value_filters.action.open')}
                removeLabel={translate('akeneo_catalogs.product_value_filters.action.remove')}
                placeholder={translate('akeneo_catalogs.product_value_filters.locale.placeholder')}
                onChange={v => onChange({...productValueFilters, locale: v})}
                onNextPage={fetchNextPage}
                invalid={isInvalid}
                data-testid='product-value-filter-by-locale'
            >
                {locales?.map(option => (
                    <MultiSelectInput.Option key={option.code} title={option.label} value={option.code}>
                        {option.label}
                    </MultiSelectInput.Option>
                ))}
            </MultiSelectInput>
        </>
    );
};
