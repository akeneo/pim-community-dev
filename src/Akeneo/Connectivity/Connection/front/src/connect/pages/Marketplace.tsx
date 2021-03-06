import React, {FC, useContext, useEffect, useState} from 'react';
import {AkeneoThemedProps, Breadcrumb, ChannelsIllustration, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import styled from 'styled-components';
import {useFetchMarketplaceUrl} from '../hooks/use-fetch-marketplace-url';
import {useRouter} from '../../shared/router/use-router';
import {UserContext} from '../../shared/user';
import {UserProfileSelector} from '../components/UserProfileSelector';
import {useSaveUserProfile} from '../hooks/use-save-user';

const LinkButton = styled.a<AkeneoThemedProps>`
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border-width: 1px;
    font-size: ${getFontSize('default')};
    font-weight: 400;
    text-transform: uppercase;
    border-radius: 16px;
    border-style: none;
    padding: 0 15px;
    height: 32px;
    cursor: pointer;
    font-family: inherit;
    transition: background-color 0.1s ease;
    outline-style: none;
    text-decoration: none;
    white-space: nowrap;
    margin-top: 10px;

    color: ${getColor('white')};
    background-color: ${getColor('purple', 100)};

    &:hover {
        background-color: ${getColor('purple', 120)};
    }

    &:active {
        background-color: ${getColor('purple', 140)};
    }
    &:focus {
        box-shadow: 0 0 0 2px ${getColor('blue', 40)};
    }
`;

const PageContent = styled.div`
    text-align: center;
    margin-top: 196px;

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

const Caption = styled.p`
    font-size: 23px;
    line-height: 1.2em;
`;

export const Marketplace: FC = () => {
    const translate = useTranslate();
    const user = useContext(UserContext);
    const saveUser = useSaveUserProfile(user.get<{id: string}>('meta').id);
    const fetchMarketplaceUrl = useFetchMarketplaceUrl();
    const [marketplaceUrl, setMarketplaceUrl] = useState<string>('');
    const [userProfile, setUserProfile] = useState<string | null | undefined>(undefined);
    const [showSelector, setShowSelector] = useState<boolean>(false);
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.marketplace')}</Breadcrumb.Step>
        </Breadcrumb>
    );

    useEffect(() => {
        const profile = user.get<string | null>('profile');
        setUserProfile(profile);
        if (null === profile) {
            setShowSelector(true);
        }
    }, [user]);

    useEffect(() => {
        fetchMarketplaceUrl().then(setMarketplaceUrl);
    }, [fetchMarketplaceUrl]);

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
            setShowSelector(false);
            fetchMarketplaceUrl().then((url: string) => {
                window.open(url);
            });
        });
    };

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                {translate('pim_menu.item.marketplace')}
            </PageHeader>

            <PageContent>
                <ChannelsIllustration size={256} />

                <Heading>{translate('akeneo_connectivity.connection.connect.marketplace.title')}</Heading>

                {showSelector ? (
                    <UserProfileSelector
                        selectedProfile={userProfile}
                        handleOnSelectChange={handleOnSelectChange}
                        handleClick={handleClick}
                    />
                ) : (
                    <>
                        <Caption>{translate('akeneo_connectivity.connection.connect.marketplace.sub_title')}</Caption>
                        <LinkButton href={marketplaceUrl} target='_blank' role='link' tabIndex='0'>
                            {translate('akeneo_connectivity.connection.connect.marketplace.link')}
                        </LinkButton>
                    </>
                )}
            </PageContent>
        </>
    );
};
