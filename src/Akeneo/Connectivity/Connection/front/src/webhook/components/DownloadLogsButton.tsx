import React, {FC} from 'react';
import {Button} from 'akeneo-design-system';
import {Translate} from '../../shared/translate';
import {useRouter} from '../../shared/router/use-router';
import {EventSubscription} from '../hooks/api/use-fetch-event-subscription';

type DownloadLogsButtonProps = {
    eventSubscription?: EventSubscription;
    disabled?: boolean;
};

export const DownloadLogsButton: FC<DownloadLogsButtonProps> = ({eventSubscription, disabled}) => {
    const generateUrl = useRouter();

    const url =
        undefined !== eventSubscription
            ? generateUrl('akeneo_connectivity_connection_events_api_debug_rest_download_event_subscription_logs', {
                  connection_code: eventSubscription.connectionCode,
              })
            : undefined;

    return (
        <Button
            href={url}
            disabled={disabled || !eventSubscription?.enabled || false}
            ghost
            level='tertiary'
            size='default'
            target='_blank'
        >
            <Translate id='akeneo_connectivity.connection.webhook.download_logs' />
        </Button>
    );
};
