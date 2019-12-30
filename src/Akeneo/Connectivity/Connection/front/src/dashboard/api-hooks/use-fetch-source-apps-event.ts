import {useEffect} from 'react';
import {AuditEventType} from '../../domain/audit/audit-event-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {sourceAppsEventFetched} from '../actions/dashboard-actions';
import {useDashboardState} from '../dashboard-state-context';
import {SourceAppsData} from '../model/source-apps-data';

type ResultValue = SourceAppsData;

export const useFetchSourceAppsEvent = (eventType: AuditEventType): SourceAppsData => {
    const [state, dispatch] = useDashboardState();

    const route = useRoute('akeneo_apps_audit_source_apps_event');

    useEffect(() => {
        fetchResult<ResultValue, never>(`${route}?event_type=${eventType}`).then(result => {
            if (isOk(result)) {
                dispatch(sourceAppsEventFetched(eventType, result.value));
            }
        });
    }, [route, eventType]);

    return state.events[eventType];
};
