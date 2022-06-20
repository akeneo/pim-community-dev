import React, {forwardRef, PropsWithRef, useCallback, useImperativeHandle, useState} from 'react';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {useTabBar} from 'akeneo-design-system';
import {TabBar, Tabs} from './TabBar';
import {ProductSelection} from '../../ProductSelection';
import {Settings} from './Settings';
import {useCatalogCriteria} from '../../ProductSelection/hooks/useCatalogCriteria';
import {CatalogEditRef} from '../CatalogEdit';
import {Criteria} from '../../ProductSelection/models/Criteria';
import {useSaveCriteria} from '../../ProductSelection/hooks/useSaveCriteria';
import {Operator} from '../../ProductSelection/models/Operator';
import {StatusCriterionState} from '../../ProductSelection/criteria/StatusCriterion';

type Props = {
    id: string;
};

const Edit = forwardRef<CatalogEditRef, PropsWithRef<Props>>(({id}, ref) => {
    const [activeTab, setActiveTab] = useSessionStorageState<string>(Tabs.SETTINGS, 'pim_catalog_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);
    const saveCriteria = useSaveCriteria(id);

    useImperativeHandle(ref, () => ({
        save() {
            const criteria: StatusCriterionState[] = [{
                field: 'status',
                operator: Operator.EQUALS,
                value: false
            }];
            saveCriteria.mutate(criteria);
        },
    }));

    const catalogCriteria = useCatalogCriteria(id);
    const [criteria, setCriteria] = useState<Criteria>(catalogCriteria);

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
