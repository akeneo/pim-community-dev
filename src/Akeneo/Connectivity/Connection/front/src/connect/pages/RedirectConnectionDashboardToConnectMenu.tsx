import {ClientErrorIllustration, Breadcrumb} from 'akeneo-design-system';
import React from 'react';
import {Translate, useTranslate} from '../../shared/translate';
import {HelperLink, PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import styled from 'styled-components';
import {Heading} from '../../common/components/EmptyState';
import {useRouter} from '../../shared/router/use-router';

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

                <Heading>{translate('akeneo_connectivity.connection.connect.redirect.title')}</Heading>

                <HelperLink href={`#${generateUrl('akeneo_connectivity_connection_audit_index')}`}>
                    <Translate id='akeneo_connectivity.connection.connect.redirect.link' />
                </HelperLink>
            </PageContent>
        </>
    );
};
