import React, {FC} from 'react';
import {ProductValueFiltersValues} from './models/FilterValuesValues';
import {FilterChannel} from './components/FilterChannel';
import {CatalogFormErrors} from '../CatalogEdit/models/CatalogFormErrors';

type Props = {
    productValueFilters: ProductValueFiltersValues
    onChange: (values: ProductValueFiltersValues) => void;
    errors: CatalogFormErrors;
}

export const ProductValueFilters: FC<Props> = ({productValueFilters, onChange, errors}) => {
    //isInvalid={errors}
    return (<>
        <FilterChannel productValueFilters={productValueFilters} onChange={onChange} />
    </>);
};
