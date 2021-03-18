import React, {FC, useEffect} from 'react';
import {FormContext, useForm, useFormContext} from 'react-hook-form';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import defaultImageUrl from '../../common/assets/illustrations/NewAPI.svg';
import {ApplyButton, PageContent, PageHeader} from '../../common/components';
import {Loading} from '../../common/components/Loading';
import {useMediaUrlGenerator} from '../../settings/use-media-url-generator';
import {isErr} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {EditForm} from '../components/EditForm';
import {EventSubscriptionHelper} from '../components/EventSubscriptionHelper';
import {useUpdateWebhook} from '../hooks/api/use-update-webhook';
import {useFetchEventSubscription} from '../hooks/api/use-fetch-event-subscription';
import {Webhook} from '../model/Webhook';
import {Breadcrumb, SectionTitle, Button} from 'akeneo-design-system';
import {useFetchConnection} from '../hooks/api/use-fetch-connection';
import {UserButtons} from '../../shared/user';

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
    const {connection} = useFetchConnection(connectionCode);
    const {eventSubscription, eventSubscriptionsLimit, fetchEventSubscription} = useFetchEventSubscription(
        connectionCode
    );

    useEffect(() => {
        fetchEventSubscription();
    }, [fetchEventSubscription]);

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

    return (
        <FormContext {...formMethods}>
            <form>
                <PageHeader
                    breadcrumb={breadcrumb}
                    userButtons={<UserButtons />}
                    imageSrc={
                        null === connection.image ? defaultImageUrl : generateMediaUrl(connection.image, 'thumbnail')
                    }
                    buttons={[
                        <SaveButton key={0} webhook={eventSubscription} onSaveSuccess={fetchEventSubscription} />,
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
