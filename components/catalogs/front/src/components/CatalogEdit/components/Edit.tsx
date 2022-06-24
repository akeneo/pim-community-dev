import React, {forwardRef, PropsWithRef, useCallback, useImperativeHandle} from 'react';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {useTabBar} from 'akeneo-design-system';
import {TabBar, Tabs} from './TabBar';
import {ProductSelection} from '../../ProductSelection';
import {Settings} from './Settings';
import {useEditableCatalogCriteria} from '../hooks/useEditableCatalogCriteria';
import {CatalogEditRef} from '../CatalogEdit';
import {useSaveCriteria} from '../../ProductSelection/hooks/useSaveCriteria';

type Props = {
    id: string;
    onChange: (isDirty: boolean) => void;
};

const Edit = forwardRef<CatalogEditRef, PropsWithRef<Props>>(({id, onChange}, ref) => {
    const [activeTab, setActiveTab] = useSessionStorageState<string>(Tabs.SETTINGS, 'pim_catalog_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);
    const [criteria, setCriteria] = useEditableCatalogCriteria(id);
    /* istanbul ignore next */
    const saveCriteria = useSaveCriteria(
        id,
        () => {
            onChange(false);
        },
        () => {
            onChange(true);
        }
    );

    useImperativeHandle(ref, () => ({
        save: () => {
            if (undefined !== criteria) {
                saveCriteria.mutate(criteria.map(value => value.state));
            }
            onChange(false);
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
            {isCurrent(Tabs.PRODUCT_SELECTION) && undefined !== criteria && undefined !== setCriteria && (
                <ProductSelection criteria={criteria} setCriteria={setCriteria} onChange={onChange} />
            )}
        </>
    );
});

export {Edit};
