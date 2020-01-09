import {AuditEventType} from '../../model/audit-event-type.enum';
import {ConnectionsAuditData} from '../model/connections-audit-data';
import {SourceConnection} from '../model/source-connection';

export const SOURCE_CONNECTIONS_FETCHED = 'SOURCE_CONNECTIONS_FETCHED';
type SourceConnectionsFetchedAction = {
    type: typeof SOURCE_CONNECTIONS_FETCHED;
    payload: SourceConnection[];
};
export const sourceConnectionsFetched = (payload: SourceConnection[]): SourceConnectionsFetchedAction => ({
    type: SOURCE_CONNECTIONS_FETCHED,
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

export type Actions = SourceConnectionsFetchedAction | ConnectionsAuditDataFetchedAction;
