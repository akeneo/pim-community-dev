import React, {useContext} from 'react';
import {EmptyState, Typography} from '../../common';
import {RouterContext} from '../../shared/router';
import {SecurityGuard} from '../../shared/security';
import {Translate} from '../../shared/translate';

type Props = {
    flowType?: 'data_source' | 'data_destination' | 'default';
    small?: true;
};

export const NoConnection = ({small, flowType = 'default'}: Props) => {
    const {redirect} = useContext(RouterContext);

    return (
        <EmptyState.EmptyState>
            <EmptyState.Illustration illustration='graph' width={small ? 80 : 200} />

            <EmptyState.Heading fontSize={small && 'default'}>
                <Translate id={`akeneo_connectivity.connection.dashboard.no_connection.title.${flowType}`} />
            </EmptyState.Heading>

            <EmptyState.Caption fontSize={small && 'default'}>
                <SecurityGuard
                    acl='akeneo_connectivity_connection_manage_settings'
                    fallback={
                        <>
                            <Translate id='akeneo_connectivity.connection.dashboard.no_connection.message_without_permission.message' />
                            &nbsp;
                            <Typography.Link
                                href='https://help.akeneo.com/pim/articles/what-is-a-connection.html'
                                target='_blank'
                            >
                                <Translate id='akeneo_connectivity.connection.dashboard.no_connection.message_without_permission.link' />
                            </Typography.Link>
                        </>
                    }
                >
                    <Translate id='akeneo_connectivity.connection.dashboard.no_connection.message_with_permission.message' />
                    &nbsp;
                    <Typography.Link onClick={() => redirect('/connect/connection-settings')}>
                        <Translate id='akeneo_connectivity.connection.dashboard.no_connection.message_with_permission.link' />
                    </Typography.Link>
                </SecurityGuard>
            </EmptyState.Caption>
        </EmptyState.EmptyState>
    );
};
