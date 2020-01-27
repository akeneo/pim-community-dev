import React, {useEffect} from 'react';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {Connection} from '../../model/connection';
import {FlowType} from '../../model/flow-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {sourceConnectionsFetched} from '../actions/dashboard-actions';
import {useDashboardDispatch, useDashboardState} from '../dashboard-context';
import {blueTheme, purpleTheme} from '../event-chart-themes';
import {SourceConnection} from '../model/source-connection';
import {EventChart} from './EventChart';
import {NoConnection} from './NoConnection';
import {UserSurvey} from './UserSurvey';

export const Charts = () => {
    const dispatch = useDashboardDispatch();

    const route = useRoute('akeneo_connectivity_connection_rest_list');
    useEffect(() => {
        fetchResult<Connection[], never>(route).then(result => {
            if (isOk(result)) {
                const sourceConnections = result.value.filter(
                    (connection): connection is SourceConnection => FlowType.DATA_SOURCE === connection.flowType
                );

                dispatch(sourceConnectionsFetched(sourceConnections));
            }
        });
    }, [route, dispatch]);

    const state = useDashboardState();
    if (0 === Object.keys(state.sourceConnections).length) {
        return <NoConnection />;
    }

    return (
        <>
            <EventChart
                eventType={AuditEventType.PRODUCT_CREATED}
                theme={purpleTheme}
                title={<Translate id='akeneo_connectivity.connection.dashboard.charts.number_of_products_created' />}
            />
            <EventChart
                eventType={AuditEventType.PRODUCT_UPDATED}
                theme={blueTheme}
                title={<Translate id='akeneo_connectivity.connection.dashboard.charts.number_of_products_updated' />}
            />

            <UserSurvey />
        </>
    );
};
