import React, {FC} from 'react';
import {EmptyState} from '../../../../common';
import {Translate} from '../../../../shared/translate';

export const NotDataSourceConnectedApp: FC = () => {
    return (
        <EmptyState.EmptyState>
            <EmptyState.Illustration illustration='api' />
            <EmptyState.Heading>
                <Translate id='akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.not_data_source' />
            </EmptyState.Heading>
        </EmptyState.EmptyState>
    );
};
