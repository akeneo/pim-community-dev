import React, {useEffect} from 'react';
import {Connection} from '../../model/connection';
import {FlowType} from '../../model/flow-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {connectionsFetched} from '../actions/dashboard-actions';
import {useDashboardDispatch, useDashboardState} from '../dashboard-context';
import {SourceConnection} from '../model/source-connection';
import {NoConnection} from './NoConnection';
import {UserSurvey} from './UserSurvey';
import {DataSourceCharts} from './DataSourceCharts';
import {DestinationConnection} from '../model/destination-connection';
import {DataDestinationCharts} from '../components/DataDestinationCharts';

export const Charts = () => {
    const dispatch = useDashboardDispatch();

    const route = useRoute('akeneo_connectivity_connection_rest_list');
    useEffect(() => {
        let cancelled = false;
        fetchResult<Connection[], never>(route).then(result => {
            if (isOk(result) && !cancelled) {
                const sourceConnections = result.value.filter(
                    (connection): connection is SourceConnection => FlowType.DATA_SOURCE === connection.flowType
                );
                const destinationConnections = result.value.filter(
                    (connection): connection is DestinationConnection =>
                        FlowType.DATA_DESTINATION === connection.flowType
                );

                dispatch(connectionsFetched({source: sourceConnections, destination: destinationConnections}));
            }
        });
        return () => {
            cancelled = true;
        };
    }, [route, dispatch]);

    const state = useDashboardState();

    return (
        <>
            {0 === Object.keys(state.destinationConnections).length &&
                0 === Object.keys(state.destinationConnections).length && <NoConnection />}
            {0 !== Object.keys(state.sourceConnections).length && <DataSourceCharts />}
            {0 !== Object.keys(state.destinationConnections).length && <DataDestinationCharts />}

            <UserSurvey />
        </>
    );
};
