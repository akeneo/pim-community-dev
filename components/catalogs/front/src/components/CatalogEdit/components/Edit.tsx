import React, {FC, PropsWithChildren} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {TabBar, useTabBar} from 'akeneo-design-system';

type Props = {
    id: string;
};

const productSelectionTabName = '#connected-app-catalog-product-selection';

const Edit: FC<PropsWithChildren<Props>> = ({id}) => {
    const translate = useTranslate();
    const [activeTab, setActiveTab] = useSessionStorageState(
        productSelectionTabName,
        'pim_connectedAppCatalog_activeTab'
    );
    const [isCurrent, switchTo] = useTabBar(activeTab);

    return (
        <>
            <TabBar moreButtonTitle='More'>
                <TabBar.Tab
                    isActive={isCurrent(productSelectionTabName)}
                    onClick={() => {
                        setActiveTab(productSelectionTabName);
                        switchTo(productSelectionTabName);
                    }}
                >
                    {translate('akeneo_catalogs.catalog_edit.tabs.product_selection')}
                </TabBar.Tab>
            </TabBar>

            {isCurrent(productSelectionTabName) && <div>product selection for catalog {id}</div>}
        </>
    );
};

export {Edit};
