import React, {FC, PropsWithChildren, useCallback} from 'react';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {useTabBar} from 'akeneo-design-system';
import {ProductSelection} from '../../ProductSelection';
import {TabBar, Tabs} from './TabBar';

type Props = {
    id: string;
};

const Edit: FC<PropsWithChildren<Props>> = () => {
    const [activeTab, setActiveTab] = useSessionStorageState<string>(Tabs.PRODUCT_SELECTION, 'pim_catalog_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);

    const handleSwitchTo = useCallback((tab: string) => {
        setActiveTab(tab);
        switchTo(tab);
    }, [setActiveTab, switchTo]);

    return (
        <>
            <TabBar
                isCurrent={isCurrent}
                switchTo={handleSwitchTo}
            />

            {isCurrent(Tabs.PRODUCT_SELECTION) && <ProductSelection />}
        </>
    );
};

export {Edit};
