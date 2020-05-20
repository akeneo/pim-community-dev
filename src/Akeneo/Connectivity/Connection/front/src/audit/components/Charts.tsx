import React, {useEffect} from 'react';
import {Connection} from '../../model/connection';
import {FlowType} from '../../model/flow-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {connectionsFetched} from '../actions/dashboard-actions';
import {DataDestinationCharts} from '../components/DataDestinationCharts';
import {useDashboardDispatch, useDashboardState} from '../dashboard-context';
import {DataSourceCharts} from './DataSourceCharts';
import {BusinessErrorCountWidget} from './ErrorManagement/BusinessErrorCountWidget';
import {NoConnection} from './NoConnection';
import {UserSurvey} from './UserSurvey';

export const Charts = () => {
    const dispatch = useDashboardDispatch();

    const route = useRoute('akeneo_connectivity_connection_rest_list');
    useEffect(() => {
        let cancelled = false;
        fetchResult<Connection[], never>(route).then(result => {
            if (isOk(result) && !cancelled) {
                const auditableConnections = result.value.filter(connection => connection.auditable);

                dispatch(connectionsFetched(auditableConnections));
            }
        });
        return () => {
            cancelled = true;
        };
    }, [route, dispatch]);

    const state = useDashboardState();
    const sourceConnections = Object.values(state.connections).filter(
        connection => FlowType.DATA_SOURCE === connection.flowType
    );
    const destinationConnections = Object.values(state.connections).filter(
        connection => FlowType.DATA_DESTINATION === connection.flowType
    );

    if (0 === sourceConnections.length && 0 === destinationConnections.length) {
        return <NoConnection />;
    }

    let orderedCharts = (
        <>
            <DataSourceCharts />
            <DataDestinationCharts />
        </>
    );
    if (0 === sourceConnections.length && 0 !== destinationConnections.length) {
        orderedCharts = (
            <>
                <DataDestinationCharts />
                <DataSourceCharts />
            </>
        );
    }

    return (
        <>
            {orderedCharts}
            <BusinessErrorCountWidget />
            <UserSurvey />
        </>
    );
};
