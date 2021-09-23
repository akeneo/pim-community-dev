import React, {FC} from 'react';
import {Breadcrumb, CheckRoundIcon, Helper, SectionTitle, TabBar} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import styled from 'styled-components';
import {ConnectedApp} from '../../../model/connected-app';
import {useRouter} from '../../../shared/router/use-router';
import {useMediaUrlGenerator} from '../../../settings/use-media-url-generator';
import {PageContent, PageHeader} from '../../../common';
import {UserButtons} from '../../../shared/user';
import {ScopeItem, ScopeList} from '../ScopeList';

const ScopeListContainer = styled.div`
  margin: 10px 20px;
`;

type Props = {
    connectedApp: ConnectedApp;
};

export const ConnectedAppContainer: FC<Props> = ({connectedApp}) => {
    const translate = useTranslate();
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedAppsListHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps')}`;
    const generateMediaUrl = useMediaUrlGenerator();
    // connectedApp.scopes = [
    //     {
    //         icon: 'catalog_structure',
    //         type: 'view',
    //         entities: 'catalog_structure'
    //     },
    //     {
    //         icon: 'attribute_options',
    //         type: 'view',
    //         entities: 'attribute_options'
    //     },
    //     {
    //         icon: 'categories',
    //         type: 'edit',
    //         entities: 'categories'
    //     }
    // ];
    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step href={connectedAppsListHref}>{translate('pim_menu.item.connected_apps')}</Breadcrumb.Step>
            <Breadcrumb.Step>{connectedApp.name}</Breadcrumb.Step>
        </Breadcrumb>
    );

    const informationLinkAnchor = translate(
        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information_link_anchor'
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
                            <CheckRoundIcon size={24} />
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
