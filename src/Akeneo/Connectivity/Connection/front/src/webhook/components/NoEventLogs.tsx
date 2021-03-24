import {GraphIllustration} from 'akeneo-design-system';
import React, {FC} from 'react';
import {EmptyState} from '../../common';
import {useTranslate} from '../../shared/translate';

const NoEventLogs: FC = () => {
    const translate = useTranslate();

    return (
        <EmptyState.EmptyState>
            <GraphIllustration size={200} />

            <EmptyState.Heading>
                {translate('akeneo_connectivity.connection.webhook.event_logs.no_event_logs.title')}
            </EmptyState.Heading>
        </EmptyState.EmptyState>
    );
};

export {NoEventLogs};
