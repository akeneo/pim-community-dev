import React from 'react';
import {useFetchSourceApps} from '../api-hooks/use-fetch-source-apps';
import {EventChart} from './EventChart';
import {NoApp} from './NoApp';
import {Translate} from '../../shared/translate';
import {AuditEventType} from '../../domain/audit/audit-event-type.enum';
import {EventChartThemes} from '../../apps/EventChartThemes';

export const Charts = () => {
    const sourceApps = useFetchSourceApps();

    if (0 === Object.keys(sourceApps).length) {
        return <NoApp />;
    }

    return (
        <>
            <EventChart
                eventType={AuditEventType.PRODUCT_CREATED}
                theme={EventChartThemes.purple}
                title={<Translate id='akeneo_connectivity.connection.dashboard.charts.number_of_products_created' />}
            />
            <EventChart
                eventType={AuditEventType.PRODUCT_UPDATED}
                theme={EventChartThemes.blue}
                title={<Translate id='akeneo_connectivity.connection.dashboard.charts.number_of_products_updated' />}
            />
        </>
    );
};
