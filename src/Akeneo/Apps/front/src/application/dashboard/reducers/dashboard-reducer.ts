import {Reducer} from 'react';
import {Actions, SOURCE_APPS_EVENT_FETCHED, SOURCE_APPS_FETCHED} from '../actions/dashboard-actions';
import {SourceApp} from '../model/source-app';
import {SourceAppsData} from '../model/source-apps-data';
import {AuditEventType} from '../../../domain/audit/audit-event-type.enum';

type SourceAppMap = {
    [code: string]: SourceApp;
};

export type State = {
    sourceApps: SourceAppMap;
    events: {
        [eventType in AuditEventType]: SourceAppsData;
    };
};

export const reducer: Reducer<State, Actions> = (state, action) => {
    switch (action.type) {
        case SOURCE_APPS_FETCHED:
            return {
                ...state,
                sourceApps: action.payload.reduce((apps, app) => {
                    apps[app.code] = app;
                    return apps;
                }, {} as SourceAppMap),
            };
        case SOURCE_APPS_EVENT_FETCHED:
            return {
                ...state,
                events: {
                    ...state.events,
                    [action.payload.eventType]: action.payload.sourceAppsData,
                },
            };
    }

    return state;
};

export const initialState: State = {
    sourceApps: {},
    events: {
        [AuditEventType.PRODUCT_CREATED]: {},
    },
};
