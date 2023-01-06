import React, {FC, useState} from 'react';
import {Search} from 'akeneo-design-system';
import {ProductSelectionValues} from '../ProductSelection';
import {ProductValueFiltersValues} from '../ProductValueFilters';
import {ProductMapping as ProductMappingType} from '../ProductMapping/models/ProductMapping';
import {useAffectedProductsQuery} from './useAffectedProducts';
import {ErrorBoundary} from '../ErrorBoundary';
import {PreviewContainer} from './components/PreviewContainer';

type Props = {
    catalogId: string,
    productSelectionCriteria: ProductSelectionValues,
    productValueFilters: ProductValueFiltersValues,
    productMapping: ProductMappingType,
};
export const CatalogPreview: FC<Props> = ({catalogId, productSelectionCriteria, productMapping}) => {
    const [productId, setProductId] = useState<string>('');

    return <>
        <ProductSelector productSelectionCriteria={productSelectionCriteria} productId={productId} setProductId={setProductId}/>
        <ErrorBoundary>
            <PreviewContainer catalogId={catalogId} productId={productId} productMapping={productMapping} />
        </ErrorBoundary>
    </>;
};

type SelectorProps = {
    productSelectionCriteria: ProductSelectionValues,
    productId: string,
    setProductId: React.Dispatch<React.SetStateAction<string>>
}
const ProductSelector: FC<SelectorProps> = ({productSelectionCriteria,productId, setProductId}) => {
    // const [search, setSearch] = useState<string>('');
    // const {data} = useAffectedProductsQuery(productSelectionCriteria, search);

    return (
        <div>
            <Search
                onSearchChange={setProductId}
                placeholder="Search"
                searchValue={productId}
                title="Search"
            />
        </div>
    );
};

