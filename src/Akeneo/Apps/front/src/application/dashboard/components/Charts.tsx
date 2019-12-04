import React, {useEffect} from 'react';
import {App} from '../../../domain/apps/app.interface';
import {FlowType} from '../../../domain/apps/flow-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {appsFetched} from '../actions/apps-actions';
import {useAppsState} from '../app-state-context';
import {SourceApp} from '../model/source-app';

export const Charts = () => {
    const [apps, dispatch] = useAppsState();

    const route = useRoute('akeneo_apps_list_rest');
    useEffect(() => {
        fetchResult<App[], never>(route).then(result => {
            if (isOk(result)) {
                dispatch(
                    appsFetched(result.value.filter((app): app is SourceApp => FlowType.DATA_SOURCE === app.flowType))
                );
            }
        });
    }, [route]);

    if (0 === Object.keys(apps).length) {
        return <>No app</>;
    }

    return <>{/* <EventChart /> */}</>;
};
