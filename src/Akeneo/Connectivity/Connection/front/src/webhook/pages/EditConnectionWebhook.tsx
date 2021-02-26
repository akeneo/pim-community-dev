import React, {FC, useEffect} from 'react';
import {FormContext, useForm, useFormContext} from 'react-hook-form';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import defaultImageUrl from '../../common/assets/illustrations/NewAPI.svg';
import {ApplyButton, PageContent, PageHeader} from '../../common/components';
import {Loading} from '../../common/components/Loading';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {useMediaUrlGenerator} from '../../settings/use-media-url-generator';
import {isErr} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {EditForm} from '../components/EditForm';
import {EventSubscriptionHelper} from '../components/EventSubscriptionHelper';
import {useUpdateWebhook} from '../hooks/api/use-update-webhook';
import {useFetchEventSubscriptionFormData} from '../hooks/api/use-fetch-event-subscription-form-data';
import {Webhook} from '../model/Webhook';
import {Breadcrumb, SectionTitle, Button} from 'akeneo-design-system';
import {useFetchConnection} from '../hooks/api/use-fetch-connection';

export type FormInput = {
    connectionCode: string;
    url: string | null;
    enabled: boolean;
    secret: string | null;
};

export const EditConnectionWebhook: FC = () => {
    const history = useHistory();
    const generateMediaUrl = useMediaUrlGenerator();
    const formMethods = useForm<FormInput>();
    const systemHref = `#${useRoute('oro_config_configuration_system')}`;

    const {connectionCode} = useParams<{connectionCode: string}>();
    const {data: connection} = useFetchConnection(connectionCode);
    const {
        eventSubscription,
        eventSubscriptionsLimit,
        fetchEventSubscriptionFormData,
    } = useFetchEventSubscriptionFormData(connectionCode);

    useEffect(() => {
        fetchEventSubscriptionFormData();
    }, [fetchEventSubscriptionFormData]);

    useEffect(() => {
        if (eventSubscription) {
            formMethods.reset(eventSubscription);
        }
    }, [eventSubscription]);

    if (!connection || !eventSubscription || !eventSubscriptionsLimit) {
        return <Loading />;
    }

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={systemHref}>
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

    const userButtons = (
        <PimView
            className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
            viewName='pim-connectivity-connection-user-navigation'
        />
    );

    return (
        <FormContext {...formMethods}>
            <form>
                <PageHeader
                    breadcrumb={breadcrumb}
                    userButtons={userButtons}
                    imageSrc={
                        null === connection.image ? defaultImageUrl : generateMediaUrl(connection.image, 'thumbnail')
                    }
                    buttons={[
                        <DownloadLogsButton key={0} webhook={eventSubscription} />,
                        <SaveButton
                            key={1}
                            webhook={eventSubscription}
                            onSaveSuccess={fetchEventSubscriptionFormData}
                        />,
                    ]}
                    state={<FormState />}
                >
                    {connection.label}
                </PageHeader>

                <PageContent>
                    <Layout>
                        <SectionTitle>
                            <SectionTitle.Title>
                                <Translate id='akeneo_connectivity.connection.webhook.event_subscription' />
                            </SectionTitle.Title>
                        </SectionTitle>
                        <EventSubscriptionHelper />
                        <EditForm webhook={eventSubscription} activeEventSubscriptionsLimit={eventSubscriptionsLimit} />
                    </Layout>
                </PageContent>
            </form>
        </FormContext>
    );
};

type SaveButtonProps = {
    webhook: Webhook;
    onSaveSuccess: () => void;
};

const SaveButton: FC<SaveButtonProps> = ({webhook, onSaveSuccess}) => {
    const {formState, getValues, triggerValidation, handleSubmit, setError} = useFormContext<FormInput>();
    const updateWebhook = useUpdateWebhook(webhook.connectionCode);
    const handleSave = async () => {
        const {enabled, url} = getValues();

        const isValid = await triggerValidation();
        if (isValid) {
            const result = await updateWebhook({
                connectionCode: webhook.connectionCode,
                enabled,
                url: '' === url ? null : url,
            });
            if (isErr(result)) {
                result.error.errors.forEach(error => {
                    setError(error.field, 'validation', error.message);
                });

                return;
            }

            onSaveSuccess();
        }
    };

    return (
        <ApplyButton
            disabled={!formState.dirty || formState.isSubmitting}
            classNames={['AknButtonList-item']}
            onClick={handleSubmit(handleSave)}
        >
            <Translate id='pim_common.save' />
        </ApplyButton>
    );
};

type DownloadLogsButtonProps = {
    webhook: Webhook;
};

const DownloadLogsButton: FC<DownloadLogsButtonProps> = ({webhook}) => {
    const url = useRoute('akeneo_connectivity_connection_events_api_debug_rest_download_event_subscription_logs', {
        connection_code: webhook.connectionCode,
    });
    return (
        <Button disabled={!webhook.enabled} ghost href={url} level='tertiary' size='default' target='_blank'>
            <Translate id='akeneo_connectivity.connection.webhook.download_logs' />
        </Button>
    );
};

const FormState = () => {
    const {formState} = useFormContext();

    return (
        (formState.dirty && (
            <div className='updated-status'>
                <span className='AknState'>
                    <Translate id='pim_common.entity_updated' />
                </span>
            </div>
        )) ||
        null
    );
};

const Layout = styled.div`
    width: 50%;
`;
