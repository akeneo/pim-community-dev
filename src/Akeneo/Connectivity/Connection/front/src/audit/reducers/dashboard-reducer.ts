import {Reducer} from 'react';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {Actions, CONNECTIONS_FETCHED, CONNECTIONS_AUDIT_DATA_FETCHED} from '../actions/dashboard-actions';
import {ConnectionsAuditData} from '../model/connections-audit-data';
import {Connection} from '../../model/connection';

type ConnectionMap = {
    [code: string]: Connection;
};

export type State = {
    connections: ConnectionMap;
    events: {
        [eventType in AuditEventType]: ConnectionsAuditData;
    };
};

export const reducer: Reducer<State, Actions> = (state, action) => {
    switch (action.type) {
        case CONNECTIONS_FETCHED:
            return {
                ...state,
                connections: action.payload.reduce((connections, connection) => {
                    connections[connection.code] = connection;
                    return connections;
                }, {} as ConnectionMap),
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
    connections: {},
    events: {
        [AuditEventType.PRODUCT_CREATED]: {},
        [AuditEventType.PRODUCT_UPDATED]: {},
        [AuditEventType.PRODUCT_READ]: {},
    },
};
