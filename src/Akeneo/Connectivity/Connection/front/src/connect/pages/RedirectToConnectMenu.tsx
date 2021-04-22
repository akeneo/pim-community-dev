import {ClientErrorIllustration, Link, Breadcrumb} from 'akeneo-design-system';
import React, {FC} from 'react';
import {useTranslate} from '../../shared/translate';
import {PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import styled from 'styled-components';
import {Heading} from '../../common/components/EmptyState';

type Props = {
    url: string;
};

const PageContent = styled.div`
    text-align: center;
    margin-top: 100px;
    & > * {
        margin-bottom: 20px;
    }
`;

export const RedirectToConnectMenu: FC<Props> = ({url}) => {
    const translate = useTranslate();
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

                <Link href={url}>{translate('akeneo_connectivity.connection.connect.redirect.click_here')}</Link>
            </PageContent>
        </>
    );
};
