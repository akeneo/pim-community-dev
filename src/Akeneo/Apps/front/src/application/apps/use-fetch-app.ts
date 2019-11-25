import {useMemo} from 'react';
import {FlowType} from '../../domain/apps/flow-type.enum';
import {useFetch} from '../shared/fetch';
import {isOk, ok} from '../shared/fetch/result';
import {useRoute} from '../shared/router';

export const useFetchApp = (code: string) => {
    const result = useFetch<{
        code: string;
        label: string;
        flow_type: FlowType;
    }>(useRoute('akeneo_apps_get_rest', {code}));

    return useMemo(() => {
        if (isOk(result)) {
            return ok({
                code: result.data.code,
                label: result.data.label,
                flowType: result.data.flow_type,
            });
        }

        return result;
    }, [result]);
};
