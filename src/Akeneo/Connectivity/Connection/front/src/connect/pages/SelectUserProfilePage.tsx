import React, {FC, useContext, useEffect, useState} from 'react';
import {Breadcrumb, ChannelsIllustration} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import styled from 'styled-components';
import {useRouter} from '../../shared/router/use-router';
import {UserContext} from '../../shared/user';
import {UserProfileSelector} from '../components/UserProfileSelector';
import {useSaveUserProfile} from '../hooks/use-save-user';
import {useHistory} from 'react-router';

const PageContent = styled.div`
    text-align: center;

    & > * {
        margin-bottom: 20px;
    }
`;

const Heading = styled.h1`
    color: ${({theme}) => theme.color.grey140};
    font-size: 28px;
    font-weight: normal;
    margin: 0;
    margin-bottom: 21px;
    line-height: 1.2em;
`;

export const SelectUserProfilePage: FC = () => {
    const translate = useTranslate();
    const user = useContext(UserContext);
    const saveUser = useSaveUserProfile(user.get<{id: string}>('meta').id);
    const [userProfile, setUserProfile] = useState<string | null | undefined>(undefined);
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const history = useHistory();

    useEffect(() => {
        const profile = user.get<string | null>('profile');

        if (null !== profile) {
            history.push('/connect/app-store');
        }
        setUserProfile(profile);
    }, [user]);

    if (undefined === userProfile) {
        return null;
    }

    const handleOnSelectChange = (selectedValue: string | null) => {
        setUserProfile(selectedValue);
    };

    const handleClick = () => {
        if (null === userProfile) {
            return;
        }
        saveUser({profile: userProfile}).then(() => {
            user.refresh().then(() => {
                history.push('/connect/app-store');
            });
        });
    };

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
                <ChannelsIllustration size={256} />

                <Heading>{translate('akeneo_connectivity.connection.connect.marketplace.title')}</Heading>

                <UserProfileSelector
                    selectedProfile={userProfile}
                    handleOnSelectChange={handleOnSelectChange}
                    handleClick={handleClick}
                />
            </PageContent>
        </>
    );
};
