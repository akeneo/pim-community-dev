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
import {findFirstError} from '../utils/findFirstError';
import {ProductSelectionErrors} from '../../ProductSelection/ProductSelection';

type Props = {
    values: CatalogFormValues;
    errors: CatalogFormErrors;
};

const mapProductSelectionCriteriaErrors = (errors: CatalogFormErrors, keys: string[]): ProductSelectionErrors => {
    const map: ProductSelectionErrors = {};

    keys.forEach((key, index) => {
        map[key] = {
            field: findFirstError(errors, `[product_selection_criteria][${index}][field]`),
            operator: findFirstError(errors, `[product_selection_criteria][${index}][operator]`),
            value: findFirstError(errors, `[product_selection_criteria][${index}][value]`),
        };
    });

    return map;
};

const Edit: FC<PropsWithChildren<Props>> = ({values, errors}) => {
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

    const handleProductSelectionChange = useCallback(
        value => {
            dispatch({type: CatalogFormActions.SET_PRODUCT_SELECTION_CRITERIA, value: value});
        },
        [dispatch]
    );

    const invalidTabs = {
        [Tabs.SETTINGS]: errors.find(error => error.propertyPath === '[enabled]') !== undefined,
        [Tabs.PRODUCT_SELECTION]:
            errors.find(error => error.propertyPath.startsWith('[product_selection_criteria]')) !== undefined,
    };

    if (undefined === values) {
        return null;
    }

    return (
        <>
            <TabBar isCurrent={isCurrent} switchTo={handleSwitchTo} invalid={invalidTabs} />

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
        </>
    );
};

export {Edit};
