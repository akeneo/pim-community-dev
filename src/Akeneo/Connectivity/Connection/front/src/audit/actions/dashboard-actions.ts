import {AuditEventType} from '../../model/audit-event-type.enum';
import {ConnectionsAuditData} from '../model/connections-audit-data';
import {Connection} from '../../model/connection';

export const CONNECTIONS_FETCHED = 'CONNECTIONS_FETCHED';
type ConnectionsFetchedAction = {
    type: typeof CONNECTIONS_FETCHED;
    payload: Connection[];
};
export const connectionsFetched = (payload: Connection[]): ConnectionsFetchedAction => ({
    type: CONNECTIONS_FETCHED,
    payload,
});

export const CONNECTIONS_AUDIT_DATA_FETCHED = 'CONNECTIONS_AUDIT_DATA_FETCHED';
type ConnectionsAuditDataFetchedAction = {
    type: typeof CONNECTIONS_AUDIT_DATA_FETCHED;
    payload: {
        eventType: AuditEventType;
        data: ConnectionsAuditData;
    };
};
export const connectionsAuditDataFetched = (
    eventType: AuditEventType,
    data: ConnectionsAuditData
): ConnectionsAuditDataFetchedAction => ({
    type: CONNECTIONS_AUDIT_DATA_FETCHED,
    payload: {
        eventType,
        data,
    },
});

export type Actions = ConnectionsFetchedAction | ConnectionsAuditDataFetchedAction;
