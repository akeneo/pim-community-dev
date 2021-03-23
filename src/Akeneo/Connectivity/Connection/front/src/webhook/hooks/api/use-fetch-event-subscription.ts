import {useCallback, useState} from 'react';
import {fetchResult} from '../../../shared/fetch-result';
import {isErr} from '../../../shared/fetch-result/result';
import {useRoute} from '../../../shared/router';

export type EventSubscription = {
    connectionCode: string;
    enabled: boolean;
    secret: string | null;
    url: string | null;
};

type EventSubscriptionsLimit = {
    limit: number;
    current: number;
};

type FormData = {
    event_subscription: EventSubscription;
    active_event_subscriptions_limit: EventSubscriptionsLimit;
};

export const useFetchEventSubscription = (connectionCode: string) => {
    const url = useRoute('akeneo_connectivity_connection_webhook_rest_get', {code: connectionCode});

    const [eventSubscription, setEventSubscription] = useState<EventSubscription>();
    const [eventSubscriptionsLimit, setEventSubscriptionsLimit] = useState<EventSubscriptionsLimit>();

    const fetchEventSubscription = useCallback(() => {
        fetchResult<FormData, unknown>(url).then(result => {
            if (isErr(result)) {
                throw new Error(`Webhook for connection '${connectionCode}' not found.`);
            }

            setEventSubscription(result.value.event_subscription);
            setEventSubscriptionsLimit(result.value.active_event_subscriptions_limit);
        });
    }, [url]);

    return {eventSubscription, eventSubscriptionsLimit, fetchEventSubscription};
};
