import {Reducer} from 'react';
import {AppCredentials} from '../../../domain/apps/app-credentials.interface';
import {App} from '../../../domain/apps/app.interface';
import {Actions, APP_WITH_CREDENTIALS_FETCHED, APP_UPDATED, APPS_FETCHED} from '../actions/apps-actions';

export interface State {
    [code: string]: App & AppCredentials;
}

export const reducer: Reducer<State, Actions> = (state, action) => {
    switch (action.type) {
        case APPS_FETCHED:
            console.log(action);
            return {
                ...state,
                ...action.payload.reduce(
                    (apps, app) => ({
                        ...apps,
                        [app.code]: {
                            ...state[app.code],
                            clientId: '',
                            secret: '',
                            username: '',
                            password: null,
                            ...app,
                        },
                    }),
                    {}
                ),
            };

        case APP_WITH_CREDENTIALS_FETCHED:
            return {
                ...state,
                [action.payload.code]: {
                    ...state[action.payload.code],
                    ...action.payload,
                    password: action.payload.password || state[action.payload.code]?.password || null,
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
