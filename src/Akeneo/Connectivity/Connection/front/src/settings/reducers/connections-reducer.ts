import {Reducer} from 'react';
import {Connection} from '../../model/connection';
import {ConnectionCredentials} from '../../model/connection-credentials';
import {ConnectionUserPermissions} from '../../model/connection-user-permissions';
import {
    Actions,
    CONNECTIONS_FETCHED,
    CONNECTION_DELETED,
    CONNECTION_FETCHED,
    CONNECTION_PASSWORD_REGENERATED,
    CONNECTION_UPDATED,
} from '../actions/connections-actions';

export interface State {
    [code: string]: Connection & ConnectionCredentials & ConnectionUserPermissions;
}

export const reducer: Reducer<State, Actions> = (state, action) => {
    switch (action.type) {
        case CONNECTIONS_FETCHED:
            return {
                ...state,
                ...action.payload.reduce(
                    (connections, connection) => ({
                        ...connections,
                        [connection.code]: {
                            clientId: '',
                            secret: '',
                            username: '',
                            password: null,
                            ...state[connection.code],
                            ...connection,
                        },
                    }),
                    {}
                ),
            };

        case CONNECTION_FETCHED:
            return {
                ...state,
                [action.payload.code]: {
                    ...state[action.payload.code],
                    ...action.payload,
                    password: action.payload.password || state[action.payload.code]?.password || null,
                },
            };

        case CONNECTION_UPDATED:
            return {
                ...state,
                [action.payload.code]: {
                    ...state[action.payload.code],
                    ...action.payload,
                },
            };

        case CONNECTION_DELETED: {
            delete state[action.payload];
            return state;
        }

        case CONNECTION_PASSWORD_REGENERATED:
            return {
                ...state,
                [action.payload.code]: {
                    ...state[action.payload.code],
                    password: action.payload.password,
                },
            };
    }
};

export const initialState: State = {};
