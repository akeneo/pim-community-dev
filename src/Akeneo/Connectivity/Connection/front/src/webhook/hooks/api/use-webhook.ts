import {useQuery} from '../../../shared/fetch';
import {Webhook} from '../../model/Webhook';

const useWebhook = (connectionCode: string) => {
    const {loading, data} = useQuery<Webhook>('akeneo_connectivity_connection_webhook_rest_get', {
        code: connectionCode,
    });

    if (!loading && !data) {
        throw new Error(`Webhook for connection '${connectionCode}' not found.`);
    }

    return {loading, webhook: data};
};

export {useWebhook};
