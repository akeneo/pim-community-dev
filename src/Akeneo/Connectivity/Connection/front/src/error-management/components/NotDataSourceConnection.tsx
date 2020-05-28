import React, {FC} from 'react';
import {EmptyState} from '../../common';
import {Translate} from '../../shared/translate';

const NotDataSourceConnection: FC = () => {
    return (
        <EmptyState.EmptyState>
            <EmptyState.Illustration illustration='api' />
            <EmptyState.Heading>
                <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.not_data_source.title' />
            </EmptyState.Heading>
        </EmptyState.EmptyState>
    );
};

export {NotDataSourceConnection};
