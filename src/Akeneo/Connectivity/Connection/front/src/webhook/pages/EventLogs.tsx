import {Breadcrumb, Button} from 'akeneo-design-system';
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
import {useRouter} from '../../shared/router/use-router';

export const EventLogs: FC = () => {
    const generateUrl = useRouter();
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
                    buttons={[
                        <DownloadLogsButton key={0} disabled={true}/>,
                    ]}
                />
                <PageContent>
                    <Loading />
                </PageContent>
            </>
        );
    }

    const downloadUrl = generateUrl('akeneo_connectivity_connection_events_api_debug_rest_download_event_subscription_logs', {
        connection_code: eventSubscription.connectionCode
    });

    return (
        <>
            <PageHeader
                breadcrumb={<EventLogsBreadcrumb />}
                userButtons={<UserButtons />}
                buttons={[
                    <DownloadLogsButton key={0} href={downloadUrl} disabled={!eventSubscription.enabled}/>,
                ]}
            >
                {connection.label}
            </PageHeader>
            <PageContent>
                {
                    eventSubscription.enabled
                        ? <EventLogList connectionCode={connectionCode} />
                        : <EventSubscriptionDisabled connectionCode={connectionCode} />
                }
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
                <Translate id='akeneo_connectivity.connection.webhook.title' />
            </Breadcrumb.Step>
        </Breadcrumb>
    );
};

type DownloadLogsButtonProps = {
    disabled?: boolean;
    href?: string
};

const DownloadLogsButton: FC<DownloadLogsButtonProps> = (props) => {
    return (
        <Button {...props} ghost level='tertiary' size='default' target='_blank'>
            <Translate id='akeneo_connectivity.connection.webhook.download_logs' />
        </Button>
    );
};
