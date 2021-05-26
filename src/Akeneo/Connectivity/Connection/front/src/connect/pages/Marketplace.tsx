import React, {FC, useEffect, useState} from 'react';
import {AkeneoThemedProps, Breadcrumb, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import styled from 'styled-components';
import {useFetchMarketingUrl} from '../hooks/use-fetch-marketing-url';
import {useRouter} from '../../shared/router/use-router';

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
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.marketplace')}</Breadcrumb.Step>
        </Breadcrumb>
    );

    const pimSource = 'pimSource';
    const nativeAppUrl = 'http://localhost:8080/#/connect/marketplace';
    const yellExtensionRedirectUrl = `${nativeAppUrl}?pim=${pimSource}`;

    useEffect(() => {
        fetchMarketplaceUrl().then(setMarketplaceUrl);
    }, [fetchMarketplaceUrl]);

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                {translate('pim_menu.item.marketplace')}
            </PageHeader>

            <PageContent>
                <LinkButton href={yellExtensionRedirectUrl} target='_blank' role='link' tabIndex='0'>
                    ACTIVATE YELL EXTENSION
                </LinkButton>
            </PageContent>
        </>
    );
};
