import React, {useContext} from 'react';
import styled from 'styled-components';
import {PageError} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {RouterContext} from '../../shared/router';
import {SecurityGuard} from '../../shared/security';
import {Translate, useTranslate} from '../../shared/translate';

export const NoConnection = () => {
    const translate = useTranslate();
    const {redirect} = useContext(RouterContext);

    return (
        <PageError title={<Translate id='akeneo_connectivity.connection.dashboard.no_connection.title' />}>
            <SecurityGuard
                acl='akeneo_connectivity_connection_manage_settings'
                fallback={
                    <>
                        <Translate id='akeneo_connectivity.connection.dashboard.no_connection.message_without_permission.message' />
                        &nbsp;
                        <Link
                            href={translate(
                                'akeneo_connectivity.connection.dashboard.no_connection.message_without_permission.link_url'
                            )}
                            target='_blank'
                        >
                            <Translate id='akeneo_connectivity.connection.dashboard.no_connection.message_without_permission.link' />
                        </Link>
                    </>
                }
            >
                <Link onClick={() => redirect('/connections')}>
                    <Translate id='akeneo_connectivity.connection.dashboard.no_connection.message_with_permission.link' />
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
