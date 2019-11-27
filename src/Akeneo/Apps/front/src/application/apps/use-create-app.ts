import {useContext} from 'react';
import {useHistory} from 'react-router';
import {FlowType} from '../../domain/apps/flow-type.enum';
import {fetch} from '../shared/fetch';
import {isErr} from '../shared/fetch/result';
import {NotificationLevel, useNotify} from '../shared/notify';
import {useRoute} from '../shared/router';
import {TranslateContext} from '../shared/translate';

export interface CreateAppData {
    code: string;
    label: string;
    flow_type: FlowType;
}

interface ResultData {
    code: string;
    label: string;
    flow_type: FlowType;
    client_id: string;
    secret: string;
    username: string;
    password: string;
}

interface ResultError {
    message: string;
    errors: Array<{
        name: string;
        reason: string;
    }>;
}

export const useCreateApp = () => {
    const url = useRoute('akeneo_apps_create_rest');
    const notify = useNotify();
    const translate = useContext(TranslateContext);
    const history = useHistory();

    return async (data: CreateAppData) => {
        const result = await fetch<ResultData, ResultError>(url, {
            method: 'POST',
            headers: [['Content-type', 'application/json']],
            body: JSON.stringify(data),
        });

        if (isErr(result)) {
            if (undefined === result.error.errors) {
                notify(NotificationLevel.ERROR, translate('pim_apps.create_app.flash.error'));
            }

            return result;
        }

        notify(NotificationLevel.SUCCESS, translate('pim_apps.create_app.flash.success'));
        history.push(`/apps/${data.code}/edit`);

        return result;
    };
};
