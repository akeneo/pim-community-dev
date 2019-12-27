import {FlowType} from '../../../domain/apps/flow-type.enum';
import {useRoute} from '../../shared/router';
import {fetchResult} from '../../shared/fetch-result';

type ResultValue = Array<{code: string; label: string; flowType: FlowType}>;

export const useFetchApps = () => {
    const url = useRoute('akeneo_apps_list_rest');

    return () => fetchResult<ResultValue, never>(url);
};
