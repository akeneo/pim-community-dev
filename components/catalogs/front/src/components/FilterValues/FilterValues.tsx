import React, {FC} from 'react';
import {FilterValuesValues} from './models/FilterValuesValues';
import {FilterChannel} from './components/FilterChannel';
import {CatalogFormErrors} from '../CatalogEdit/models/CatalogFormErrors';

type Props = {
    filterValues: FilterValuesValues
    onChange: (values: FilterValuesValues) => void;
    errors: CatalogFormErrors;
}

export const FilterValues: FC<Props> = ({filterValues, onChange, errors}) => {
    //isInvalid={errors}
    return (<>
        <FilterChannel filterValues={filterValues} onChange={onChange} />
    </>);
};
