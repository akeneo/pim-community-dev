import React, {useEffect} from 'react';
import {Connection} from '../../model/connection';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {connectionsFetched} from '../actions/dashboard-actions';
import {useDashboardDispatch, useDashboardState} from '../dashboard-context';
import {NoConnection} from './NoConnection';
import {UserSurvey} from './UserSurvey';
import {DataSourceCharts} from './DataSourceCharts';
import {DataDestinationCharts} from '../components/DataDestinationCharts';
import {FlowType} from '../../model/flow-type.enum';

export const Charts = () => {
    const dispatch = useDashboardDispatch();

    const route = useRoute('akeneo_connectivity_connection_rest_list');
    useEffect(() => {
        let cancelled = false;
        fetchResult<Connection[], never>(route).then(result => {
            if (isOk(result) && !cancelled) {
                dispatch(connectionsFetched(result.value));
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

    let render = <NoConnection />;
    const displayNone = 0 === sourceConnections.length && 0 === destinationConnections.length;
    const displayOne = !displayNone && (0 === sourceConnections.length || 0 === destinationConnections.length);
    const displaySourceFirst = displayOne && 0 !== sourceConnections.length;
    const displayDestinationFirst = displayOne && 0 !== destinationConnections.length;
    const displayAll = 0 !== sourceConnections.length && 0 !== destinationConnections.length;
    if (displayAll || displaySourceFirst) {
        render =
            <>
                <DataSourceCharts />
                <DataDestinationCharts />
                <UserSurvey />
            </>;
    } else if (displayDestinationFirst) {
        render =
            <>
                <DataDestinationCharts />
                <DataSourceCharts />
                <UserSurvey />
            </>;
    }

    return <>{render}</>;
};
