import {useContext} from 'react';
import {useHistory} from 'react-router';
import {FlowType} from '../../model/flow-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isErr} from '../../shared/fetch-result/result';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useRoute} from '../../shared/router';
import {TranslateContext} from '../../shared/translate';

export interface CreateConnectionData {
    code: string;
    label: string;
    flow_type: FlowType;
}

interface ResultValue {
    code: string;
    label: string;
    flow_type: FlowType;
    image: string | null;
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

export const useCreateConnection = () => {
    const url = useRoute('akeneo_connectivity_connection_rest_create');
    const notify = useNotify();
    const translate = useContext(TranslateContext);
    const history = useHistory();

    return async (data: CreateConnectionData) => {
        const result = await fetchResult<ResultValue, ResultError>(url, {
            method: 'POST',
            headers: [['Content-type', 'application/json']],
            body: JSON.stringify(data),
        });

        if (isErr(result)) {
            if (undefined === result.error.errors) {
                notify(
                    NotificationLevel.ERROR,
                    translate('akeneo_connectivity.connection.create_connection.flash.error')
                );
            }

            return result;
        }

        notify(NotificationLevel.SUCCESS, translate('akeneo_connectivity.connection.create_connection.flash.success'));
        history.push(`/connections/${data.code}/edit`);

        return result;
    };
};
