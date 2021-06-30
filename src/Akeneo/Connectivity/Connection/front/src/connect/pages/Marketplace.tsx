import React, {FC, useContext, useEffect, useState} from 'react';
import {AkeneoThemedProps, Breadcrumb, ChannelsIllustration, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import styled from 'styled-components';
import {useRouter} from '../../shared/router/use-router';
import {UserContext} from '../../shared/user';
import {useHistory} from 'react-router';

const PageContent = styled.div`
    text-align: center;
    margin-top: 196px;

    & > * {
        margin-bottom: 20px;
    }
`;

export const Marketplace: FC = () => {
    const translate = useTranslate();
    const user = useContext(UserContext);
    const history = useHistory();
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const [userProfile, setUserProfile] = useState<string | null >(null);

    useEffect(() => {
        const profile = user.get<string | null>('profile');
        if (null === profile) {
            history.push('/connect/marketplace/profile');
        } else {
            setUserProfile(profile);
        }
    }, [user]);

    if (null === userProfile) {
        return null;
    }

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.marketplace')}</Breadcrumb.Step>
        </Breadcrumb>
    );

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                {translate('pim_menu.item.marketplace')}
            </PageHeader>

            <PageContent>
                Extensions go here
            </PageContent>
        </>
    );
};
