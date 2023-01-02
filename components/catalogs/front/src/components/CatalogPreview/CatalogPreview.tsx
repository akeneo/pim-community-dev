import React, {FC, useState} from 'react';
import {Button, Dropdown, GroupsIllustration, Search} from 'akeneo-design-system';
import {ProductSelectionValues} from '../ProductSelection';
import {ProductValueFiltersValues} from '../ProductValueFilters';
import {ProductMapping as ProductMappingType} from '../ProductMapping/models/ProductMapping';
import {useAffectedProductsQuery} from './useAffectedProducts';

type Props = {
    catalogId: string,
    productSelectionCriteria: ProductSelectionValues,
    productValueFilters: ProductValueFiltersValues,
    productMapping: ProductMappingType,
};
export const CatalogPreview: FC<Props> = ({productSelectionCriteria}) => {
    return (<div>
       <ProductSelector productSelectionCriteria={productSelectionCriteria}/>
    </div>);
};

type SelectorProps = {
    productSelectionCriteria: ProductSelectionValues
}
const ProductSelector: FC<SelectorProps> = ({productSelectionCriteria}) => {
    const [search, setSearch] = useState<string>('');
    const {data} = useAffectedProductsQuery(productSelectionCriteria, search);

    console.log(data);

    return (
        <div>
            <Search
                onSearchChange={setSearch}
                placeholder="Search"
                searchValue={search}
                title="Search"
            />
        </div>
    );
};

