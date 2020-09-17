import {fetchResult} from '../../../shared/fetch-result';
import {useRoute} from '../../../shared/router';
import {Webhook} from '../../model/Webhook';
import {WebhookAccessibility} from '../../model/WebhookAccessibility';

const useCheckAccessibility = (webhook: Webhook, url: string) => {
    const code = webhook.connectionCode;
    const route = useRoute('akeneo_connectivity_connection_webhook_rest_check_accessibility', {code});

    return async () => {
        const result = await fetchResult<WebhookAccessibility, undefined>(route, {
            method: 'POST',
            headers: [['Content-type', 'application/json']],
            body: JSON.stringify({
                url: url,
            }),
        });

        return result;
    };
};

export {useCheckAccessibility};
