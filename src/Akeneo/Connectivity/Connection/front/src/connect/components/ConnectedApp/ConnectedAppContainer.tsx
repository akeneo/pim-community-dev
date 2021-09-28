import React, {FC} from 'react';
import {Breadcrumb, TabBar, useTabBar} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {useRouter} from '../../../shared/router/use-router';
import {useMediaUrlGenerator} from '../../../settings/use-media-url-generator';
import {PageContent, PageHeader} from '../../../common';
import {UserButtons} from '../../../shared/user';
import {ConnectedAppSettings} from './ConnectedAppSettings';
import {useSessionStorageState} from '@akeneo-pim-community/shared';
import {ConnectedAppPermissions} from './ConnectedAppPermissions';
import {usePermissionFormRegistry} from '../../../shared/permission-form-registry';

type Props = {
    connectedApp: ConnectedApp;
};

const settingsTabName = '#connected-app-tab-settings';
const permissionsTabName = '#connected-app-tab-permissions';

export const ConnectedAppContainer: FC<Props> = ({connectedApp}) => {
    const translate = useTranslate();
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedAppsListHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps')}`;
    const generateMediaUrl = useMediaUrlGenerator();
    const permissionFormRegistry = usePermissionFormRegistry();
    const [activeTab, setActiveTab] = useSessionStorageState(settingsTabName, 'pim_connectedApp_activeTab');
    const [isCurrent, switchTo] = useTabBar(activeTab);

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step href={connectedAppsListHref}>{translate('pim_menu.item.connected_apps')}</Breadcrumb.Step>
            <Breadcrumb.Step>{connectedApp.name}</Breadcrumb.Step>
        </Breadcrumb>
    );

    return (
        <>
            <PageHeader
                breadcrumb={breadcrumb}
                userButtons={<UserButtons />}
                imageSrc={generateMediaUrl(connectedApp.logo, 'thumbnail')}
            >
                {connectedApp.name}
            </PageHeader>

            <PageContent>
                <TabBar moreButtonTitle='More'>
                    <TabBar.Tab
                        isActive={isCurrent(settingsTabName)}
                        onClick={() => {
                            setActiveTab(settingsTabName);
                            switchTo(settingsTabName);
                        }}
                    >
                        {translate('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')}
                    </TabBar.Tab>
                    {permissionFormRegistry.countProviders() > 0 && (
                        <TabBar.Tab
                            isActive={isCurrent(permissionsTabName)}
                            onClick={() => {
                                setActiveTab(permissionsTabName);
                                switchTo(permissionsTabName);
                            }}
                        >
                            {translate('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')}
                        </TabBar.Tab>
                    )}
                </TabBar>

                {isCurrent(settingsTabName) && <ConnectedAppSettings connectedApp={connectedApp} />}

                {isCurrent(permissionsTabName) && <ConnectedAppPermissions connectedApp={connectedApp} />}
            </PageContent>
        </>
    );
};
