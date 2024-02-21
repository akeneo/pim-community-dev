import {useContext} from 'react';
import {fetchResult} from '../../../shared/fetch-result';
import {isErr} from '../../../shared/fetch-result/result';
import {NotificationLevel, useNotify} from '../../../shared/notify';
import {useRoute} from '../../../shared/router';
import {TranslateContext} from '../../../shared/translate';

export type RequestData = {
    connectionCode: string;
    enabled: boolean;
    url: string | null;
    isUsingUuid: boolean;
};

type ResultError = {
    message: string;
    errors: Array<{
        field: 'connectionCode' | 'url' | 'enabled' | 'isUsingUuid';
        message: string;
    }>;
};
type ResultOk = {
    connectionCode: string;
    url: string | null;
    secret: string | null;
    enabled: boolean;
    isUsingUuid: boolean;
};

export const useUpdateWebhook = (code: string) => {
    const url = useRoute('akeneo_connectivity_connection_webhook_rest_update', {code});
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    return async (data: RequestData) => {
        const result = await fetchResult<ResultOk, ResultError>(url, {
            method: 'POST',
            headers: [['Content-type', 'application/json']],
            body: JSON.stringify({
                code: data.connectionCode,
                enabled: data.enabled,
                url: data.url,
                is_using_uuid: data.isUsingUuid,
            }),
        });
        if (isErr(result)) {
            if (result.error.errors) {
                result.error.errors.forEach(({message}) => notify(NotificationLevel.ERROR, translate(message)));
            } else {
                notify(NotificationLevel.ERROR, translate('akeneo_connectivity.connection.webhook.flash.error'));
            }

            return result;
        }

        notify(NotificationLevel.SUCCESS, translate('akeneo_connectivity.connection.webhook.flash.success'));

        return result;
    };
};
