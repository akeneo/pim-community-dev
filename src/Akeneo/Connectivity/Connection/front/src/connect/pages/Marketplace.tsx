import React, {FC} from 'react';
import {Breadcrumb, ChannelsIllustration, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageHeader} from '../../common';
import {UserButtons} from '../../shared/user';
import styled from 'styled-components';
import {Caption, Heading} from '../../common/components/EmptyState';

const LinkButton = styled.a`
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
    width: 121px;
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
    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step>
                {translate('pim_menu.connect.title')}
            </Breadcrumb.Step>
            <Breadcrumb.Step>
                {translate('pim_menu.connect.marketplace')}
            </Breadcrumb.Step>
        </Breadcrumb>
    );

    return (<>
        <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
            {translate('pim_menu.connect.marketplace')}
        </PageHeader>

        <PageContent>
            <ChannelsIllustration size={256} />

            <Heading>
                {translate('akeneo_connectivity.connection.connect.marketplace.title')}
            </Heading>

            <Caption>
                {translate('akeneo_connectivity.connection.connect.marketplace.sub_title')}
            </Caption>

            <LinkButton
                href='https://marketplace.akeneo.com/extensions?edition=all&version=all&api_use=1&sort=date'
                target='_blank'
                role='link'
                tabindex='0'
            >
                {translate('akeneo_connectivity.connection.connect.marketplace.link')}
            </LinkButton>
        </PageContent>
    </>);
};
