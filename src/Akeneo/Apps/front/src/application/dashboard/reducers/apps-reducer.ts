import {Reducer} from 'react';
import {Actions, SOURCE_APPS_FETCHED} from '../actions/apps-actions';
import {SourceApp} from '../model/source-app';

export interface State {
    [code: string]: SourceApp;
}

export const reducer: Reducer<State, Actions> = (state, action) => {
    switch (action.type) {
        case SOURCE_APPS_FETCHED:
            return action.payload.reduce((apps: State, app) => {
                apps[app.code] = app;
                return apps;
            }, {});
    }

    return state;
};
