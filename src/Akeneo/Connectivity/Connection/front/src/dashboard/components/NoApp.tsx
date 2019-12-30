import React, {useContext} from 'react';
import styled from 'styled-components';
import {PageError} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {SecurityGuard} from '../../shared/security';
import {Translate, useTranslate} from '../../shared/translate';
import {RouterContext} from '../../shared/router';

export const NoApp = () => {
    const translate = useTranslate();
    const {redirect} = useContext(RouterContext);

    return (
        <PageError title={<Translate id='akeneo_connectivity.connection.dashboard.no_app.title' />}>
            <SecurityGuard
                acl='akeneo_apps_manage_settings'
                fallback={
                    <>
                        <Translate id='akeneo_connectivity.connection.dashboard.no_app.message_without_permission.message' />
                        &nbsp;
                        <Link
                            href={translate('akeneo_connectivity.connection.dashboard.no_app.message_without_permission.link_url')}
                            target='_blank'
                        >
                            <Translate id='akeneo_connectivity.connection.dashboard.no_app.message_without_permission.link' />
                        </Link>
                    </>
                }
            >
                <Link onClick={() => redirect('/apps')}>
                    <Translate id='akeneo_connectivity.connection.dashboard.no_app.message_with_permission.link' />
                </Link>
            </SecurityGuard>
        </PageError>
    );
};

const Link = styled.a`
    color: #9452ba;
    cursor: pointer;
    text-decoration: underline ${({theme}: PropsWithTheme) => theme.color.purple100};
`;
