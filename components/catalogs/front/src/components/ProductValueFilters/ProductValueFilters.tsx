import React, {FC} from 'react';
import {ProductValueFiltersValues} from './models/FilterValuesValues';
import {FilterChannel} from './components/FilterChannel';
import {CatalogFormErrors} from '../CatalogEdit/models/CatalogFormErrors';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {getColor, getFontSize} from 'akeneo-design-system';

type Props = {
    productValueFilters: ProductValueFiltersValues;
    onChange: (values: ProductValueFiltersValues) => void;
    errors: CatalogFormErrors;
};

const FilterContainer = styled.div`
    max-width: 400px;
    margin-top: 19px;
`;
const Label = styled.div`
    font-size: ${getFontSize('default')};
    color: ${getColor('grey', 120)};
    line-height: 16px;
`;

export const ProductValueFilters: FC<Props> = ({productValueFilters, onChange, errors}) => {
    //isInvalid={errors}
    const translate = useTranslate();

    return (
        <>
            <FilterContainer>
                <Label>{translate('akeneo_catalogs.filter_values.criteria.channel.label')}</Label>
                <FilterChannel productValueFilters={productValueFilters} onChange={onChange} />
            </FilterContainer>
        </>
    );
};
