import {GraphIllustration} from 'akeneo-design-system';
import React, {FC} from 'react';
import {EmptyState} from '../../common';
import {Translate, useTranslate} from '../../shared/translate';

const NoEventLogsWithThoseFilters: FC = () => {
    const translate = useTranslate();

    return (
        <EmptyState.EmptyState>
            <GraphIllustration size={200} />
            <EmptyState.Heading>
                {translate('akeneo_connectivity.connection.webhook.event_logs.no_event_logs_with_those_filters.title')}
            </EmptyState.Heading>
            <EmptyState.Caption>
                {translate('akeneo_connectivity.connection.webhook.event_logs.no_event_logs_with_those_filters.caption')}
            </EmptyState.Caption>
        </EmptyState.EmptyState>
    );
};

export {NoEventLogsWithThoseFilters};
