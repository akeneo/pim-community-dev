import React, {FC} from 'react';
import {ProductValueFiltersValues} from './models/ProductValueFiltersValues';
import {FilterChannel} from './components/FilterChannel';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {getColor, getFontFamily, getFontSize, Helper} from 'akeneo-design-system';
import {ProductValueFiltersErrors} from './models/ProductValueFiltersErrors';
import {FilterCurrencies} from './components/FilterCurrencies';
import {FilterLocale} from './components/FilterLocale';

type Props = {
    productValueFilters: ProductValueFiltersValues;
    onChange: (values: ProductValueFiltersValues) => void;
    errors: ProductValueFiltersErrors;
};

const FilterContainer = styled.div`
    max-width: 400px;
    margin-top: 19px;
`;
const Label = styled.div`
    font-size: ${getFontSize('default')};
    color: ${getColor('grey', 120)};
    line-height: 16px;
    margin-bottom: 5px;
`;
const WarningMessage = styled.span`
    font-style: italic;
    font-size: ${getFontSize('small')};
    font-family: ${getFontFamily('default')};
`;

export const ProductValueFilters: FC<Props> = ({productValueFilters, onChange, errors}) => {
    const translate = useTranslate();

    return (
        <>
            <FilterContainer>
                <Label>{translate('akeneo_catalogs.product_value_filters.filters.channel.label')}</Label>
                <FilterChannel
                    productValueFilters={productValueFilters}
                    onChange={onChange}
                    isInvalid={!!errors.channels}
                />
                {!!errors.channels && (
                    <Helper inline level='error'>
                        <WarningMessage>{translate(errors.channels)}</WarningMessage>
                    </Helper>
                )}
            </FilterContainer>

            <FilterContainer>
                <Label>{translate('akeneo_catalogs.product_value_filters.filters.locale.label')}</Label>
                <FilterLocale
                    productValueFilters={productValueFilters}
                    onChange={onChange}
                    isInvalid={!!errors.locales}
                />
                {!!errors.locales && (
                    <Helper inline level='error'>
                        <WarningMessage>{translate(errors.locales)}</WarningMessage>
                    </Helper>
                )}
            </FilterContainer>

            <FilterContainer>
                <Label>{translate('akeneo_catalogs.product_value_filters.filters.currency.label')}</Label>
                <FilterCurrencies
                    productValueFilters={productValueFilters}
                    onChange={onChange}
                    isInvalid={!!errors.currencies}
                />
                {!!errors.currencies && (
                    <Helper inline level='error'>
                        <WarningMessage>{translate(errors.currencies)}</WarningMessage>
                    </Helper>
                )}
            </FilterContainer>
        </>
    );
};
