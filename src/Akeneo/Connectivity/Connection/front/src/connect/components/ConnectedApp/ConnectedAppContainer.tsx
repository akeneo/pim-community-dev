import React, {FC} from 'react';
import {Breadcrumb, TabBar} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {useRouter} from '../../../shared/router/use-router';
import {useMediaUrlGenerator} from '../../../settings/use-media-url-generator';
import {PageContent, PageHeader} from '../../../common';
import {UserButtons} from '../../../shared/user';
import {ConnectedAppSettings} from './ConnectedAppSettings';

type Props = {
    connectedApp: ConnectedApp;
};

export const ConnectedAppContainer: FC<Props> = ({connectedApp}) => {
    const translate = useTranslate();
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedAppsListHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps')}`;
    const generateMediaUrl = useMediaUrlGenerator();

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
                    <TabBar.Tab isActive>
                        {translate('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')}
                    </TabBar.Tab>
                </TabBar>

                <ConnectedAppSettings connectedApp={connectedApp} />
            </PageContent>
        </>
    );
};
