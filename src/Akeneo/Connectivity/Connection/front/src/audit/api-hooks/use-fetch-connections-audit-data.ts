import {useEffect} from 'react';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {connectionsAuditDataFetched} from '../actions/dashboard-actions';
import {useDashboardDispatch, useDashboardState} from '../dashboard-context';
import {ConnectionsAuditData} from '../model/connections-audit-data';

type ResultValue = ConnectionsAuditData;

export const useFetchConnectionsAuditData = (eventType: AuditEventType): ConnectionsAuditData => {
    const state = useDashboardState();
    const dispatch = useDashboardDispatch();

    const route = useRoute('akeneo_connectivity_connection_rest_audit_source_connections_event');

    useEffect(() => {
        fetchResult<ResultValue, never>(`${route}?event_type=${eventType}`).then(result => {
            if (isOk(result)) {
                dispatch(connectionsAuditDataFetched(eventType, result.value));
            }
        });
    }, [route, eventType, dispatch]);

    return state.events[eventType];
};
