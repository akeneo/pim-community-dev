import React, {FC, PropsWithChildren} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Pill, TabBar as StyledTabBar} from 'akeneo-design-system';
import {useCatalog} from '../hooks/useCatalog';

enum Tabs {
    PRODUCT_SELECTION = '#catalog-product-selection',
    PRODUCT_VALUE_FILTERS = '#catalog-product-value-filters',
    PRODUCT_MAPPING = '#catalog-product-mapping',
    PREVIEW = '#catalog-preview',
}

type Props = {
    isCurrent: (tab: string) => boolean;
    switchTo: (tab: string) => void;
    invalid: {
        [key in Tabs]: boolean;
    };
    id: string;
};

const TabBar: FC<PropsWithChildren<Props>> = ({isCurrent, switchTo, invalid, id}) => {
    const translate = useTranslate();

    const {data: catalog, isLoading} = useCatalog(id);
    const isProductMappingEditable = isLoading === false && catalog?.has_product_mapping_schema;

    return (
        <>
            <StyledTabBar moreButtonTitle={translate('akeneo_catalogs.catalog_edit.tabs.more')}>
                <StyledTabBar.Tab
                    isActive={isCurrent(Tabs.PRODUCT_SELECTION)}
                    onClick={() => switchTo(Tabs.PRODUCT_SELECTION)}
                >
                    {translate('akeneo_catalogs.catalog_edit.tabs.product_selection')}
                    {invalid[Tabs.PRODUCT_SELECTION] && <Pill level='danger' />}
                </StyledTabBar.Tab>
                {isProductMappingEditable ? (
                    <StyledTabBar.Tab
                        isActive={isCurrent(Tabs.PRODUCT_MAPPING)}
                        onClick={() => switchTo(Tabs.PRODUCT_MAPPING)}
                    >
                        {translate('akeneo_catalogs.catalog_edit.tabs.product_mapping')}
                        {invalid[Tabs.PRODUCT_MAPPING] && <Pill level='danger' />}
                    </StyledTabBar.Tab>
                ) : (
                    <StyledTabBar.Tab
                        isActive={isCurrent(Tabs.PRODUCT_VALUE_FILTERS)}
                        onClick={() => switchTo(Tabs.PRODUCT_VALUE_FILTERS)}
                    >
                        {translate('akeneo_catalogs.catalog_edit.tabs.product_value_filters')}
                        {invalid[Tabs.PRODUCT_VALUE_FILTERS] && <Pill level='danger' />}
                    </StyledTabBar.Tab>
                )}
                <StyledTabBar.Tab
                    isActive={isCurrent(Tabs.PREVIEW)}
                    onClick={() => switchTo(Tabs.PREVIEW)}
                >
                    {translate('Preview')}
                </StyledTabBar.Tab>
            </StyledTabBar>
        </>
    );
};

export {TabBar, Tabs};
