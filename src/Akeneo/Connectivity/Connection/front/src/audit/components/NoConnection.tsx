import React, {useContext} from 'react';
import styled from 'styled-components';
import {PageError} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {RouterContext} from '../../shared/router';
import {SecurityGuard} from '../../shared/security';
import {Translate} from '../../shared/translate';
import {FlowType} from '../../model/flow-type.enum';
import imgUrl from '../../common/assets/illustrations/charts.svg';

type Props = {
    flowType?: FlowType.DATA_SOURCE | FlowType.DATA_DESTINATION | 'default';
}

export const NoConnection = ({flowType = 'default'}: Props) => {
    const {redirect} = useContext(RouterContext);

    return (
        <PageError
            title={<Translate id={`akeneo_connectivity.connection.dashboard.no_connection.title.${flowType}`} />}
            imgUrl={imgUrl}
        >
            <SecurityGuard
                acl='akeneo_connectivity_connection_manage_settings'
                fallback={
                    <>
                        <Translate id='akeneo_connectivity.connection.dashboard.no_connection.message_without_permission.message' />
                        &nbsp;
                        <Link href='https://help.akeneo.com/pim/articles/what-is-a-connection.html' target='_blank'>
                            <Translate id='akeneo_connectivity.connection.dashboard.no_connection.message_without_permission.link' />
                        </Link>
                    </>
                }
            >
                <Translate id='akeneo_connectivity.connection.dashboard.no_connection.message_with_permission.message' />
                &nbsp;
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
