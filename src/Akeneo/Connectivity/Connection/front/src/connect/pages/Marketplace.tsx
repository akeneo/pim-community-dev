import React, {FC, useEffect, useState} from 'react';
import {
    AkeneoThemedProps,
    Breadcrumb,
    ChannelsIllustration,
    getColor,
    getFontSize,
    SelectInput,
    Field,
    Helper, Link
} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import styled from 'styled-components';
import {useFetchMarketingUrl} from '../hooks/use-fetch-marketing-url';
import {useRouter} from '../../shared/router/use-router';

const FetcherRegistry = require('pim/fetcher-registry');

type UserProfileEntry = {
    code: string;
    label: string;
};

const UserProfileSelector = styled(SelectInput)`

    height: 40px;
`;

const UserProfileField = styled(Field)`
    width: 400px;
    margin: 0 auto;
    text-align: left;
`;

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
    const fetchMarketplaceUrl = useFetchMarketingUrl();
    const [marketplaceUrl, setMarketplaceUrl] = useState<string>('');
    const [userProfileEntries, setUserProfileEntries] = useState<UserProfileEntry[]>([]);
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.marketplace')}</Breadcrumb.Step>
        </Breadcrumb>
    );

    useEffect(() => {
        fetchMarketplaceUrl().then(setMarketplaceUrl);
    }, [fetchMarketplaceUrl]);

    useEffect(() => {
        FetcherRegistry
            .getFetcher('user-profiles')
            .fetchAll()
            .then(setUserProfileEntries);
    }, []);

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                {translate('pim_menu.item.marketplace')}
            </PageHeader>

            <PageContent>
                <ChannelsIllustration size={256} />

                <Heading>{translate('akeneo_connectivity.connection.connect.marketplace.title')}</Heading>

                <Caption>{translate('akeneo_connectivity.connection.connect.marketplace.sub_title')}</Caption>

                <UserProfileField label={translate('pim_user_management.entity.user.properties.profile')}>
                    <UserProfileSelector value={null}
                                         emptyResultLabel={translate('pim_user.profile.selector.not_found')}
                                         placeholder={translate('pim_user.profile.selector.placeholder')}
                                         onChange={() => null}>
                        {userProfileEntries.map((profileEntry: UserProfileEntry) =>
                            <SelectInput.Option
                                key={profileEntry.code}
                                title={translate(profileEntry.label)}
                                value={profileEntry.code}
                            >{translate(profileEntry.label)}
                            </SelectInput.Option>
                        )}
                    </UserProfileSelector>
                    <Helper level="info">
                        <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
                            {translate('pim_user.profile.why_is_it_needed')}
                        </Link>
                    </Helper>
                </UserProfileField>

                <LinkButton href={marketplaceUrl} target='_blank' role='link' tabIndex='0'>
                    {translate('akeneo_connectivity.connection.connect.marketplace.link')}
                </LinkButton>
            </PageContent>
        </>
    );
};
