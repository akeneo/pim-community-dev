import React, {FC, useEffect, useState} from 'react';
import {Breadcrumb, CheckRoundIcon, Helper, SectionTitle, TabBar} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageContent, PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import {useRouter} from '../../shared/router/use-router';
import {useFeatureFlags} from '../../shared/feature-flags';
import {useParams} from 'react-router';
import {useMediaUrlGenerator} from '../../settings/use-media-url-generator';
import {useFetchConnectedApp} from '../hooks/use-fetch-connected-app';
import {ConnectedApp} from '../../model/connected-app';
import {FullScreenError} from '@akeneo-pim-community/shared';
import {ScopeItem, ScopeList} from '../components/ScopeList';
import styled from 'styled-components';

const ScopeListContainer = styled.div`
  margin: 10px 20px;
`;

export const ConnectedAppPage: FC = () => {
    const translate = useTranslate();
    const featureFlag = useFeatureFlags();
    const {connectionCode} = useParams<{connectionCode: string}>();
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedAppsListHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps')}`;
    const generateMediaUrl = useMediaUrlGenerator();
    const fetchConnectedApp = useFetchConnectedApp();
    const [connectedApp, setConnectedApp] = useState<ConnectedApp | null | false>(null);

    useEffect(() => {
        if (!featureFlag.isEnabled('marketplace_activate')) {
            setConnectedApp(false);
            return;
        }

        fetchConnectedApp
            .then(setConnectedApp)
            .catch(() => setConnectedApp(false));
    }, [fetchConnectedApp]);

    if (false === connectedApp) {
        return (
            <FullScreenError
                title={translate('error.exception', {status_code: '404'})}
                message={translate('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')}
                code={404}
            />
        );
    }

    const breadcrumb = (connectedApp &&
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step href={connectedAppsListHref}>{translate('pim_menu.item.connected_apps')}</Breadcrumb.Step>
            <Breadcrumb.Step>{connectedApp.name}</Breadcrumb.Step>
        </Breadcrumb>
    );

    const informationLinkAnchor = translate(
        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information_link_anchor'
    );

    return (connectedApp &&
        <>
            <PageHeader
                breadcrumb={breadcrumb}
                userButtons={<UserButtons />}
                imageSrc={generateMediaUrl(connectedApp.logo, 'thumbnail')}
            >
                {connectedApp.name}
            </PageHeader>

            <PageContent>
                <TabBar moreButtonTitle={'More'}>
                    <TabBar.Tab isActive={true}>
                        {translate('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')}
                    </TabBar.Tab>
                </TabBar>

                <SectionTitle>
                    <SectionTitle.Title>{translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.title')}</SectionTitle.Title>
                </SectionTitle>
                <Helper level='info'>
                    <div
                        dangerouslySetInnerHTML={{
                            __html: translate(
                                'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information',
                                {link: `<a href='https://help.akeneo.com/pim/serenity/articles/how-to-connect-my-pim-with-apps.html#all-editions-authorization-step' target='_blank'>${informationLinkAnchor}</a>`}
                            ),
                        }}
                    />
                </Helper>
                <ScopeListContainer>
                    {0 === connectedApp.scopes.length ? (
                        <ScopeItem key='0' fontSize='default'>
                            <CheckRoundIcon
                                size={24}
                                title={translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_scope')}
                            />
                            {translate('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_scope')}
                        </ScopeItem>
                    ) : (
                        <ScopeList scopeMessages={connectedApp.scopes} itemFontSize='default' />
                    )}
                </ScopeListContainer>
            </PageContent>
        </>
    );
};
