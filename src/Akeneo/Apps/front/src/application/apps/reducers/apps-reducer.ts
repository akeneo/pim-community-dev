import {Reducer} from 'react';
import {AppCredentials} from '../../../domain/apps/app-credentials.interface';
import {App} from '../../../domain/apps/app.interface';
import {Actions, APP_WITH_CREDENTIALS_FETCHED, APP_UPDATED} from '../actions/apps-actions';

export interface State {
    [code: string]: App & AppCredentials;
}

export const reducer: Reducer<State, Actions> = (state, action) => {
    switch (action.type) {
        case APP_WITH_CREDENTIALS_FETCHED:
            return {
                ...state,
                [action.payload.code]: {
                    ...state[action.payload.code],
                    ...action.payload,
                    password:
                        action.payload.password ||
                        (state[action.payload.code] && state[action.payload.code].password) ||
                        null,
                },
            };

        case APP_UPDATED:
            return {
                ...state,
                [action.payload.code]: {
                    ...state[action.payload.code],
                    ...action.payload,
                },
            };
    }

    return state;
};
