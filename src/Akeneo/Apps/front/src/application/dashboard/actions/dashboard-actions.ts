import {AuditEventType} from '../../../domain/audit/audit-event-type.enum';
import {SourceApp} from '../model/source-app';
import {SourceAppsData} from '../model/source-apps-data';

export const SOURCE_APPS_FETCHED = 'APPS_FETCHED';
type SourceAppsFetchedAction = {
    type: typeof SOURCE_APPS_FETCHED;
    payload: SourceApp[];
};
export const sourceAppsFetched = (payload: SourceApp[]): SourceAppsFetchedAction => ({
    type: SOURCE_APPS_FETCHED,
    payload,
});

export const SOURCE_APPS_EVENT_FETCHED = 'SOURCE_APPS_EVENT_FETCHED';
type SourceAppsEventFetchedAction = {
    type: typeof SOURCE_APPS_EVENT_FETCHED;
    payload: {
        eventType: AuditEventType;
        sourceAppsData: SourceAppsData;
    };
};
export const sourceAppsEventFetched = (
    eventType: AuditEventType,
    sourceAppsData: SourceAppsData
): SourceAppsEventFetchedAction => ({
    type: SOURCE_APPS_EVENT_FETCHED,
    payload: {
        eventType,
        sourceAppsData,
    },
});

export type Actions = SourceAppsFetchedAction | SourceAppsEventFetchedAction;
