import React, {FC, useEffect, useState} from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageContent, PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import {useRouter} from '../../shared/router/use-router';
import {ConnectedAppsContainerIsLoading} from "../components/ConnectedApps/ConnectedAppsContainerIsLoading";
import {ConnectedAppsContainer} from '../components/ConnectedApps/ConnectedAppsContainer';
import {ConnectedApp} from '../../model/connected-app';
import {useFetchConnectedApps} from "../hooks/use-fetch-connected-apps";
import {useFeatureFlags} from "../../shared/feature-flags";

export const ConnectedAppsListPage: FC = () => {
    const translate = useTranslate();
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const featureFlag = useFeatureFlags();
    const fetchConnectedApps = useFetchConnectedApps();
    const [connectedApps, setConnectedApps] = useState<ConnectedApp[] | null | false>(null);

    useEffect(() => {
        if (!featureFlag.isEnabled('marketplace_activate')) {
            setConnectedApps([]);
            return;
        }

        fetchConnectedApps()
            .then(setConnectedApps)
            .catch(() => setConnectedApps(false));
    }, [fetchConnectedApps]);

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.connected_apps')}</Breadcrumb.Step>
        </Breadcrumb>
    );

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                {translate('pim_menu.item.connected_apps')}
            </PageHeader>

            <PageContent>
                {null === connectedApps && <ConnectedAppsContainerIsLoading />}
                {false === connectedApps && <>Something went wrong</>}
                {false !== connectedApps && null !== connectedApps && (
                    <ConnectedAppsContainer connectedApps={connectedApps} />
                )}
            </PageContent>
        </>
    );
};
