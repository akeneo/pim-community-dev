import {Breadcrumb} from 'akeneo-design-system';
import React, {FC, useEffect} from 'react';
import {useHistory, useParams} from 'react-router-dom';
import {Loading, PageContent, PageHeader} from '../../common';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {UserButtons} from '../../shared/user';
import {EventSubscriptionDisabled} from '../components/EventSubscriptionDisabled';
import {EventLogList} from '../components/EventLogList';
import {useFetchConnection} from '../hooks/api/use-fetch-connection';
import {useFetchEventSubscription} from '../hooks/api/use-fetch-event-subscription';
import {DownloadLogsButton} from '../components/DownloadLogsButton';

export const EventLogs: FC = () => {
    const {connectionCode} = useParams<{connectionCode: string}>();
    const {connection} = useFetchConnection(connectionCode);
    const {eventSubscription, fetchEventSubscription} = useFetchEventSubscription(connectionCode);

    useEffect(() => {
        fetchEventSubscription();
    }, [fetchEventSubscription]);

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
