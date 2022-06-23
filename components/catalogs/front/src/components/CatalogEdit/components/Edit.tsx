import React, {forwardRef, PropsWithRef, useCallback, useImperativeHandle, useState} from 'react';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {useTabBar} from 'akeneo-design-system';
import {TabBar, Tabs} from './TabBar';
import {ProductSelection} from '../../ProductSelection';
import {Settings} from './Settings';
import {useCatalogCriteria} from '../../ProductSelection/hooks/useCatalogCriteria';
import {CatalogEditRef} from '../CatalogEdit';
import {Criteria, CriterionStates} from '../../ProductSelection/models/Criteria';
import {useSaveCriteria} from '../../ProductSelection/hooks/useSaveCriteria';
import {StatusCriterionState} from '../../ProductSelection/criteria/StatusCriterion';

type Props = {
    id: string;
    onChange: (isDirty: boolean) => void;
};

const Edit = forwardRef<CatalogEditRef, PropsWithRef<Props>>(({id, onChange}, ref) => {
    const [activeTab, setActiveTab] = useSessionStorageState<string>(Tabs.SETTINGS, 'pim_catalog_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);
    const catalogCriteria = useCatalogCriteria(id);
    const [criteria, setCriteria] = useState<Criteria>(catalogCriteria);
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
            const criteriaStates: CriterionStates[] = criteria.map(value => value.state);
            saveCriteria.mutate(criteriaStates);
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
            {isCurrent(Tabs.PRODUCT_SELECTION) && (
                <ProductSelection criteria={criteria} setCriteria={setCriteria} onChange={onChange} />
            )}
        </>
    );
});

export {Edit};
