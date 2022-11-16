import React, {FC, PropsWithChildren, useCallback} from 'react';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {useTabBar} from 'akeneo-design-system';
import {TabBar, Tabs} from './TabBar';
import {Settings} from './Settings';
import {CatalogFormValues} from '../models/CatalogFormValues';
import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {ProductSelection} from '../../ProductSelection';
import {useCatalogFormContext} from '../contexts/CatalogFormContext';
import {CatalogFormActions} from '../reducers/CatalogFormReducer';
import {mapProductSelectionCriteriaErrors} from '../utils/mapProductSelectionCriteriaErrors';
import {getTabsValidationStatus} from '../utils/getTabsValidationStatus';
import {ProductValueFilters} from '../../ProductValueFilters';
import {mapProductValueFiltersErrors} from '../utils/mapProductValueFiltersErrors';
import {ProductMapping} from '../../ProductMapping';
import {ProductMapping as ProductMappingType} from '../../ProductMapping/models/ProductMapping';
import {useProductMappingSchema} from '../../../hooks/useProductMappingSchema';
import {mapProductMappingSourceErrors} from '../utils/mapProductMappingSourceErrors';
import {ProductMappingErrors} from '../../ProductMapping/models/ProductMappingErrors';

type Props = {
    id: string;
    values: CatalogFormValues;
    errors: CatalogFormErrors;
};

const ProductMappingWrapper: FC<
    PropsWithChildren<{
        catalogId: string;
        productMapping: ProductMappingType;
        errors: ProductMappingErrors;
    }>
> = ({catalogId, productMapping, errors}) => {
    const {data: productMappingSchema} = useProductMappingSchema(catalogId);

    return (
        <ProductMapping productMapping={productMapping} productMappingSchema={productMappingSchema} errors={errors} />
    );
};

const Edit: FC<PropsWithChildren<Props>> = ({id, values, errors}) => {
    const dispatch = useCatalogFormContext();
    const [activeTab, setActiveTab] = useSessionStorageState<string>(Tabs.SETTINGS, 'pim_catalog_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);

    const handleSwitchTo = useCallback(
        (tab: string) => {
            setActiveTab(tab);
            switchTo(tab);
        },
        [setActiveTab, switchTo]
    );

    /* istanbul ignore next */
    const handleProductSelectionChange = useCallback(
        value => {
            dispatch({type: CatalogFormActions.SET_PRODUCT_SELECTION_CRITERIA, value: value});
        },
        [dispatch]
    );
    /* istanbul ignore next */
    const handleFilterValuesChange = useCallback(
        value => dispatch({type: CatalogFormActions.SET_PRODUCT_VALUE_FILTERS, value: value}),
        [dispatch]
    );

    return (
        <>
            <TabBar isCurrent={isCurrent} switchTo={handleSwitchTo} invalid={getTabsValidationStatus(errors)} id={id} />

            {isCurrent(Tabs.SETTINGS) && (
                <Settings
                    settings={{
                        enabled: values.enabled,
                    }}
                    errors={errors}
                />
            )}
            {isCurrent(Tabs.PRODUCT_SELECTION) && (
                <ProductSelection
                    criteria={values.product_selection_criteria}
                    onChange={handleProductSelectionChange}
                    errors={mapProductSelectionCriteriaErrors(errors, Object.keys(values.product_selection_criteria))}
                />
            )}
            {isCurrent(Tabs.PRODUCT_VALUE_FILTERS) && (
                <ProductValueFilters
                    productValueFilters={values.product_value_filters}
                    onChange={handleFilterValuesChange}
                    errors={mapProductValueFiltersErrors(errors)}
                />
            )}
            {isCurrent(Tabs.PRODUCT_MAPPING) && (
                <ProductMappingWrapper
                    catalogId={id}
                    productMapping={values.product_mapping}
                    errors={mapProductMappingSourceErrors(errors, Object.keys(values.product_mapping))}
                />
            )}
        </>
    );
};

export {Edit};
