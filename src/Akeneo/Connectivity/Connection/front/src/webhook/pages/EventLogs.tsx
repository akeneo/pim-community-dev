import {Breadcrumb} from 'akeneo-design-system';
import React, {FC, useEffect, useState} from 'react';
import {useHistory, useParams} from 'react-router-dom';
import {Loading, PageContent, PageHeader} from '../../common';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {UserButtons} from '../../shared/user';
import {EventSubscriptionDisabled} from '../components/EventSubscriptionDisabled';
import {EventLogList} from '../components/EventLogList';
import {EventSubscription} from '../hooks/api/use-fetch-event-subscription';
import {DownloadLogsButton} from '../components/DownloadLogsButton';
import {useRouter} from '../../shared/router/use-router';
import {Connection} from '../../model/connection';

type EventSubscriptionResponse = {
    connection?: Connection;
    eventSubscription?: EventSubscription;
};

const useEventSubscription = (connectionCode: string): EventSubscriptionResponse => {
    const [state, setState] = useState<EventSubscriptionResponse>({});
    const generateUrl = useRouter();
    const urlGetConnection = generateUrl(
        'akeneo_connectivity_connection_rest_get',
        {code: connectionCode}
    );
    const urlGetEventSubscription = generateUrl(
        'akeneo_connectivity_connection_webhook_rest_get',
        {code: connectionCode}
    );

    useEffect(() => {
        (async () => {
            const connection = await (await fetch(urlGetConnection)).json();
            const eventSubscription = await (await fetch(urlGetEventSubscription)).json();

            setState({connection, eventSubscription});
        })();
    }, [connectionCode]);

    return state;
};

export const EventLogs: FC = () => {
    const {connectionCode} = useParams<{connectionCode: string}>();
    const {connection, eventSubscription} = useEventSubscription(connectionCode);

    if (undefined === connection || undefined === eventSubscription) {
        return (
            <>
                <PageHeader
                    breadcrumb={<EventLogsBreadcrumb />}
                    userButtons={<UserButtons />}
                    buttons={[<DownloadLogsButton key={0} disabled={true} />]}
                />
                <PageContent>
                    <Loading />
                </PageContent>
            </>
        );
    }

    return (
        <>
            <PageHeader
                breadcrumb={<EventLogsBreadcrumb />}
                userButtons={<UserButtons />}
                buttons={[<DownloadLogsButton key={0} eventSubscription={eventSubscription} />]}
            >
                {connection.label}
            </PageHeader>
            <PageContent>
                {eventSubscription.enabled ? (
                    <EventLogList connectionCode={connectionCode} />
                ) : (
                    <EventSubscriptionDisabled connectionCode={connectionCode} />
                )}
            </PageContent>
        </>
    );
};

const EventLogsBreadcrumb: FC = () => {
    const history = useHistory();

    return (
        <Breadcrumb>
            <Breadcrumb.Step href={'#' + useRoute('oro_config_configuration_system')}>
                <Translate id='pim_menu.tab.system' />
            </Breadcrumb.Step>
            <Breadcrumb.Step href={history.createHref({pathname: '/connections'})}>
                <Translate id='pim_menu.item.connection_settings' />
            </Breadcrumb.Step>
            <Breadcrumb.Step>
                <Translate id='akeneo_connectivity.connection.webhook.event_logs.title' />
            </Breadcrumb.Step>
        </Breadcrumb>
    );
};
