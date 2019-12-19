import React from 'react';
import styled from 'styled-components';
import {PageError} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {SecurityGuard} from '../../shared/security';
import {Translate, useTranslate} from '../../shared/translate';

export const NoApp = () => {
    const translate = useTranslate();

    return (
        <PageError title={<Translate id='akeneo_apps.dashboard.no_app.title' />}>
            <SecurityGuard
                acl='akeneo_apps_manage_settings'
                fallback={
                    <>
                        <Translate id='akeneo_apps.dashboard.no_app.message_without_permission.message' />
                        &nbsp;
                        <Link href={translate('akeneo_apps.dashboard.no_app.message_without_permission.link_url')}>
                            <Translate id='akeneo_apps.dashboard.no_app.message_without_permission.link' />
                        </Link>
                    </>
                }
            >
                <Link href={translate('akeneo_apps.dashboard.no_app.message_with_permission.link_url')}>
                    <Translate id='akeneo_apps.dashboard.no_app.message_with_permission.link' />
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
