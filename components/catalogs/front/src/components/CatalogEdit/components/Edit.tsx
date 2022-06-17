import React, {FC, PropsWithChildren, useCallback} from 'react';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {useTabBar} from 'akeneo-design-system';
import {TabBar, Tabs} from './TabBar';
import {ProductSelection} from '../../ProductSelection';
import {Settings} from './Settings';

type Props = {
    id: string;
};

const Edit: FC<PropsWithChildren<Props>> = ({id}) => {
    const [activeTab, setActiveTab] = useSessionStorageState<string>(Tabs.SETTINGS, 'pim_catalog_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);

    const handleSwitchTo = useCallback(
        (tab: string) => {
            setActiveTab(tab);
            switchTo(tab);
        },
        [setActiveTab, switchTo]
    );

    return (
        <>
            <TabBar isCurrent={isCurrent} switchTo={handleSwitchTo} />

            {isCurrent(Tabs.SETTINGS) && <Settings />}
            {isCurrent(Tabs.PRODUCT_SELECTION) && <ProductSelection id={id} />}
        </>
    );
};

export {Edit};
