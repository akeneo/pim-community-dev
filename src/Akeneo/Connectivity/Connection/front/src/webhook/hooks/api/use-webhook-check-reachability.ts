import {fetchResult} from '../../../shared/fetch-result';
import {useRoute} from '../../../shared/router';
import {WebhookReachability} from '../../model/WebhookReachability';

const useCheckReachability = (code: string) => {
    const route = useRoute('akeneo_connectivity_connection_webhook_rest_check_reachability', {code});

    return async (url: string, secret: string) => {
        const result = await fetchResult<WebhookReachability, undefined>(route, {
            method: 'POST',
            headers: [['Content-type', 'application/json']],
            body: JSON.stringify({
                url: url,
                secret: secret,
            }),
        });

        return result;
    };
};

export {useCheckReachability};
