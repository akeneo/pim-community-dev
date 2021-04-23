import {Breadcrumb, ClientErrorIllustration} from 'akeneo-design-system';
import React from 'react';
import styled from 'styled-components';
import {EmptyState, HelperLink, PageHeader} from '../../common';
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

export const RedirectConnectionDashboardToConnectMenu = () => {
    const translate = useTranslate();
    const generateUrl = useRouter();

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step>{translate('pim_menu.tab.activity')}</Breadcrumb.Step>
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

                <EmptyState.Heading>
                    {translate('akeneo_connectivity.connection.connect.redirect.title')}
                </EmptyState.Heading>

                <HelperLink href={`#${generateUrl('akeneo_connectivity_connection_audit_index')}`}>
                    <Translate id='akeneo_connectivity.connection.connect.redirect.link' />
                </HelperLink>
            </PageContent>
        </>
    );
};
