import React, {FC, PropsWithChildren} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Pill, TabBar as StyledTabBar} from 'akeneo-design-system';

enum Tabs {
    SETTINGS = '#catalog-settings',
    PRODUCT_SELECTION = '#catalog-product-selection',
    FILTER_VALUES = '#catalog-filter-values',
}

type Props = {
    isCurrent: (tab: string) => boolean;
    switchTo: (tab: string) => void;
    invalid: {
        [key in Tabs]: boolean;
    };
};

const TabBar: FC<PropsWithChildren<Props>> = ({isCurrent, switchTo, invalid}) => {
    const translate = useTranslate();

    return (
        <>
            <StyledTabBar moreButtonTitle={translate('akeneo_catalogs.catalog_edit.tabs.more')}>
                <StyledTabBar.Tab isActive={isCurrent(Tabs.SETTINGS)} onClick={() => switchTo(Tabs.SETTINGS)}>
                    {translate('akeneo_catalogs.catalog_edit.tabs.settings')}
                    {invalid[Tabs.SETTINGS] && <Pill level='danger' />}
                </StyledTabBar.Tab>
                <StyledTabBar.Tab
                    isActive={isCurrent(Tabs.PRODUCT_SELECTION)}
                    onClick={() => switchTo(Tabs.PRODUCT_SELECTION)}
                >
                    {translate('akeneo_catalogs.catalog_edit.tabs.product_selection')}
                    {invalid[Tabs.PRODUCT_SELECTION] && <Pill level='danger' />}
                </StyledTabBar.Tab>
                <StyledTabBar.Tab isActive={isCurrent(Tabs.FILTER_VALUES)} onClick={() => switchTo(Tabs.FILTER_VALUES)}>
                    {translate('akeneo_catalogs.catalog_edit.tabs.filter_values')}
                    {invalid[Tabs.FILTER_VALUES] && <Pill level='danger' />}
                </StyledTabBar.Tab>
            </StyledTabBar>
        </>
    );
};

export {TabBar, Tabs};
