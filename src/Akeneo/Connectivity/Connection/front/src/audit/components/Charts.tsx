import React, {useEffect} from 'react';
import {Connection} from '../../model/connection';
import {FlowType} from '../../model/flow-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {sourceConnectionsFetched} from '../actions/dashboard-actions';
import {useDashboardDispatch, useDashboardState} from '../dashboard-context';
import {SourceConnection} from '../model/source-connection';
import {NoConnection} from './NoConnection';
import {UserSurvey} from './UserSurvey';
import {DataSourceCharts} from './DataSourceCharts';

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

                dispatch(sourceConnectionsFetched(sourceConnections));
            }
        });
        return () => {
            cancelled = true;
        };
    }, [route, dispatch]);

    const state = useDashboardState();
    if (0 === Object.keys(state.sourceConnections).length) {
        return <NoConnection />;
    }

    return (
        <>
            <DataSourceCharts />
            <UserSurvey />
        </>
    );
};
