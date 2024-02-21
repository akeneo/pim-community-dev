import React, {FC} from 'react';
import {EmptyState, Typography} from '../../../../common';
import {Translate} from '../../../../shared/translate';

export const NotAuditableConnectedApp: FC = () => {
    return (
        <EmptyState.EmptyState>
            <EmptyState.Illustration illustration='graph' />
            <EmptyState.Heading>
                <Translate id='akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.title' />
            </EmptyState.Heading>
            <EmptyState.Caption>
                <Translate id='akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.description.1' />
                &nbsp;
                <Typography.Link
                    href='https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#enable-the-tracking'
                    target='_blank'
                    rel='noopener noreferrer'
                >
                    <Translate id='akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.description.2' />
                </Typography.Link>
                &nbsp;
                <Translate id='akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_auditable.description.3' />
            </EmptyState.Caption>
        </EmptyState.EmptyState>
    );
};
