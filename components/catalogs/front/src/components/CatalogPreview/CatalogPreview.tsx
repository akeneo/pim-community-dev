import React, {FC, useState} from 'react';
import {Search} from 'akeneo-design-system';
import {ProductSelectionValues} from '../ProductSelection';
import {ProductValueFiltersValues} from '../ProductValueFilters';
import {ProductMapping as ProductMappingType} from '../ProductMapping/models/ProductMapping';
import {Product, useAffectedProductsQuery} from './useAffectedProducts';
import {ErrorBoundary} from '../ErrorBoundary';
import {PreviewContainer} from './components/PreviewContainer';
import {ProductSelectionDropdown} from './ProductSelectionDropdown';

type Props = {
    catalogId: string,
    productSelectionCriteria: ProductSelectionValues,
    productValueFilters: ProductValueFiltersValues,
    productMapping: ProductMappingType,
};

export const CatalogPreview: FC<Props> = ({catalogId, productSelectionCriteria, productMapping}) => {
    const [product, setProduct] = useState<Product>();

    return <>
        <ErrorBoundary>
            <ProductSelectionDropdown
                catalogId={catalogId}
                productSelectionCriteria={productSelectionCriteria}
                selectedProduct={product}
                onChange={selectedProduct => setProduct(selectedProduct)}
            />
            <PreviewContainer
                catalogId={catalogId} productId={product?.uuid ?? ''} productMapping={productMapping} />
        </ErrorBoundary>
    </>;
};
