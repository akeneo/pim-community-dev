import React, {forwardRef, PropsWithRef, useCallback, useImperativeHandle} from 'react';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {useTabBar} from 'akeneo-design-system';
import {TabBar, Tabs} from './TabBar';
import {ProductSelection} from '../../ProductSelection';
import {Settings} from './Settings';
import {useCriteria} from '../hooks/useCriteria';
import {CatalogEditRef} from '../CatalogEdit';

type Props = {
    id: string;
};

const Edit = forwardRef<CatalogEditRef, PropsWithRef<Props>>(({id}, ref) => {
    const [activeTab, setActiveTab] = useSessionStorageState<string>(Tabs.SETTINGS, 'pim_catalog_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);

    const [criteria, setCriteria] = useCriteria(id);

    useImperativeHandle(ref, () => ({
        save() {
            console.log('Catalog ' + id + ' saved.');
        },
    }));

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
            {isCurrent(Tabs.PRODUCT_SELECTION) && <ProductSelection criteria={criteria} setCriteria={setCriteria} />}
        </>
    );
});

export {Edit};
