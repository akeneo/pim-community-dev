import {Reducer} from 'react';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {Actions, SOURCE_CONNECTIONS_FETCHED, CONNECTIONS_AUDIT_DATA_FETCHED} from '../actions/dashboard-actions';
import {SourceConnection} from '../model/source-connection';
import {ConnectionsAuditData} from '../model/connections-audit-data';

type SourceConnectionMap = {
    [code: string]: SourceConnection;
};

export type State = {
    sourceConnections: SourceConnectionMap;
    events: {
        [eventType in AuditEventType]: ConnectionsAuditData;
    };
};

export const reducer: Reducer<State, Actions> = (state, action) => {
    switch (action.type) {
        case SOURCE_CONNECTIONS_FETCHED:
            return {
                ...state,
                sourceConnections: action.payload.reduce((connections, connection) => {
                    connections[connection.code] = connection;
                    return connections;
                }, {} as SourceConnectionMap),
            };
        case CONNECTIONS_AUDIT_DATA_FETCHED:
            return {
                ...state,
                events: {
                    ...state.events,
                    [action.payload.eventType]: action.payload.data,
                },
            };
    }

    return state;
};

export const initialState: State = {
    sourceConnections: {},
    events: {
        [AuditEventType.PRODUCT_CREATED]: {},
        [AuditEventType.PRODUCT_UPDATED]: {},
    },
};
