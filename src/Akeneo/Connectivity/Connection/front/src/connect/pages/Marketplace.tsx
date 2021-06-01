import React, {FC} from 'react';
import {AkeneoThemedProps, Breadcrumb, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import styled from 'styled-components';
import {useRouter} from '../../shared/router/use-router';
import {useAppActivate} from '../hooks/use-app-activate';
import {useRoute} from "../../shared/router";

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

export const Marketplace: FC = () => {
    const translate = useTranslate();
    //const fetchMarketplaceUrl = useFetchMarketingUrl();
    //const [marketplaceUrl, setMarketplaceUrl] = useState<string>('');
    const appActivate = useAppActivate('19e75c0ee9eb4ecf84c5d294186980ee60738a74c2be11eb85');
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.marketplace')}</Breadcrumb.Step>
        </Breadcrumb>
    );
    const activateUrl = useRoute(
        'akeneo_connectivity_connection_app_activate',
        {identifier:'19e75c0ee9eb4ecf84c5d294186980ee60738a74c2be11eb85'}
    );

    //useEffect(() => {
    //    fetchMarketplaceUrl().then(setMarketplaceUrl);
    //}, [fetchMarketplaceUrl]);

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                {translate('pim_menu.item.marketplace')}
            </PageHeader>

            <PageContent>
                <LinkButton href={activateUrl} target='_blank' role='link' tabIndex='0'>
                    ACTIVATE YELL EXTENSION
                </LinkButton>
            </PageContent>
        </>
    );
};
