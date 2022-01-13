import React, {FC} from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageContent, PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import {useRouter} from '../../shared/router/use-router';
import {ConnectedAppsContainerIsLoading} from '../components/ConnectedApps/ConnectedAppsContainerIsLoading';
import {ConnectedAppsContainer} from '../components/ConnectedApps/ConnectedAppsContainer';
import {useConnectedApps} from '../hooks/use-connected-apps';
import {DeveloperModeTag} from '../components/DeveloperModeTag';
import {useDeveloperMode} from '../hooks/use-developer-mode';

export const ConnectedAppsListPage: FC = () => {
    const translate = useTranslate();
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedApps = useConnectedApps();
    const isDeveloperModeEnabled = useDeveloperMode();

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.connected_apps')}</Breadcrumb.Step>
        </Breadcrumb>
    );

    const tag = isDeveloperModeEnabled ? <DeveloperModeTag /> : null;

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />} tag={tag}>
                {translate('pim_menu.item.connected_apps')}
            </PageHeader>

            <PageContent>
                {null === connectedApps && <ConnectedAppsContainerIsLoading />}
                {false !== connectedApps && null !== connectedApps && (
                    <ConnectedAppsContainer connectedApps={connectedApps} />
                )}
            </PageContent>
        </>
    );
};
