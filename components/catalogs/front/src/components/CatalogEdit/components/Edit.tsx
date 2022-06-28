import React, {FC, PropsWithChildren, useCallback} from 'react';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {useTabBar} from 'akeneo-design-system';
import {TabBar, Tabs} from './TabBar';
import {Settings} from './Settings';
import {CatalogFormValues} from '../models/CatalogFormValues';
import {CatalogFormErrors} from '../models/CatalogFormErrors';

type Props = {
    values: CatalogFormValues;
    errors: CatalogFormErrors;
};

const Edit: FC<PropsWithChildren<Props>> = ({values, errors}) => {
    const [activeTab, setActiveTab] = useSessionStorageState<string>(Tabs.SETTINGS, 'pim_catalog_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);

    const handleSwitchTo = useCallback(
        (tab: string) => {
            setActiveTab(tab);
            switchTo(tab);
        },
        [setActiveTab, switchTo]
    );

    if (undefined === values) {
        return null;
    }

    return (
        <>
            <TabBar isCurrent={isCurrent} switchTo={handleSwitchTo} />

            {isCurrent(Tabs.SETTINGS) && (
                <Settings
                    settings={{
                        enabled: values.enabled,
                    }}
                    errors={errors}
                />
            )}
        </>
    );
};

export {Edit};
