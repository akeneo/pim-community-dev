import {useEffect} from 'react';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {sourceAppsFetched} from '../actions/dashboard-actions';
import {useDashboardState} from '../dashboard-state-context';
import {SourceApp} from '../model/source-app';

export const useFetchSourceApps = () => {
    const [state, dispatch] = useDashboardState();

    const route = useRoute('akeneo_apps_list_rest');
    useEffect(() => {
        fetchResult<SourceApp[], never>(route).then(result => {
            if (isOk(result)) {
                dispatch(sourceAppsFetched(result.value));
            }
        });
    }, [route]);

    return state.sourceApps;
};
