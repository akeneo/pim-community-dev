import {useEffect} from 'react';
import {AuditEventType} from '../../../domain/audit/audit-event-type.enum';
import {sourceAppsEventFetched} from '../actions/dashboard-actions';
import {useDashboardState} from '../dashboard-state-context';
import {SourceAppsData} from '../model/source-apps-data';

type ResultValue = SourceAppsData;

const data: ResultValue = {
    aaa: {
        '25-11-19': 0,
        '26-11-19': 0,
        '27-11-19': 0,
        '28-11-19': 0,
        '29-11-19': 0,
        '30-11-19': 0,
        '01-12-19': 0,
        '02-12-19': 0,
    },
};

export const useFetchSourceAppsEvent = (eventType: AuditEventType): SourceAppsData => {
    const [state, dispatch] = useDashboardState();

    /*
    const route = useRoute('akeneo_apps_audit_source_apps_event', {event_type: event});
    useEffect(() => {
        fetchResult<ResultValue, never>(route).then(result => {
            if (isOk(result)) {
                dispatch(sourceAppsEventFetched(eventType, result.value));
            }
        });
    }, [route]);
    */

    useEffect(() => {
        dispatch(sourceAppsEventFetched(eventType, data));
    }, []);

    return state.events[eventType];
};
