import {Breadcrumb, ClientErrorIllustration} from 'akeneo-design-system';
import React from 'react';
import styled from 'styled-components';
import {HelperLink, PageHeader} from '../../common';
import {useRouter} from '../../shared/router/use-router';
import {Translate, useTranslate} from '../../shared/translate';
import {UserButtons} from '../../shared/user';

const PageContent = styled.div`
    text-align: center;
    margin-top: 100px;
    & > * {
        margin-bottom: 20px;
    }
`;

const Caption = styled.p`
    font-size: 23px;
    line-height: 1.2em;
`;

const Heading = styled.h1`
    color: ${({theme}) => theme.color.grey140};
    font-size: 28px;
    font-weight: normal;
    margin: 0;
    margin-bottom: 21px;
    line-height: 1.2em;
`;

export const RedirectConnectionDashboardToConnectMenu = () => {
    const translate = useTranslate();
    const generateUrl = useRouter();

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={`#${generateUrl('pim_dashboard_index')}`}>
                {translate('pim_menu.tab.activity')}
            </Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.connection_audit')}</Breadcrumb.Step>
        </Breadcrumb>
    );

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                {translate('pim_menu.item.connection_audit')}
            </PageHeader>

            <PageContent>
                <ClientErrorIllustration width={500} height={250} />

                <Heading>
                    {translate('akeneo_connectivity.connection.connect.redirect.title')}
                </Heading>
                <Caption>
                    <HelperLink href={`#${generateUrl('akeneo_connectivity_connection_audit_index')}`}>
                        <Translate id='akeneo_connectivity.connection.connect.redirect.link' />
                    </HelperLink>
                </Caption>
            </PageContent>
        </>
    );
};
