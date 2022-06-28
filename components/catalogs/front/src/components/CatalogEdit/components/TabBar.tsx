import React, {FC, PropsWithChildren} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {TabBar as StyledTabBar} from 'akeneo-design-system';

enum Tabs {
    SETTINGS = '#catalog-settings',
    PRODUCT_SELECTION = '#catalog-product-selection',
}

type Props = {
    isCurrent: (tab: string) => boolean;
    switchTo: (tab: string) => void;
};

const TabBar: FC<PropsWithChildren<Props>> = ({isCurrent, switchTo}) => {
    const translate = useTranslate();

    return (
        <>
            <StyledTabBar moreButtonTitle={translate('akeneo_catalogs.catalog_edit.tabs.more')}>
                <StyledTabBar.Tab isActive={isCurrent(Tabs.SETTINGS)} onClick={() => switchTo(Tabs.SETTINGS)}>
                    {translate('akeneo_catalogs.catalog_edit.tabs.settings')}
                </StyledTabBar.Tab>
                <StyledTabBar.Tab
                    isActive={isCurrent(Tabs.PRODUCT_SELECTION)}
                    onClick={() => switchTo(Tabs.PRODUCT_SELECTION)}
                >
                    {translate('akeneo_catalogs.catalog_edit.tabs.product_selection')}
                </StyledTabBar.Tab>
            </StyledTabBar>
        </>
    );
};

export {TabBar, Tabs};
