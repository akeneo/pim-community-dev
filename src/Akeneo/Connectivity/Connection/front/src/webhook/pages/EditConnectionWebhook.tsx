import React, {FC, useEffect, useState} from 'react';
import {FormContext, useForm, useFormContext} from 'react-hook-form';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import defaultImageUrl from '../../common/assets/illustrations/NewAPI.svg';
import {ApplyButton, PageContent, PageHeader, Section} from '../../common/components';
import {Loading} from '../../common/components/Loading';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {useMediaUrlGenerator} from '../../settings/use-media-url-generator';
import {isErr} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {EditForm} from '../components/EditForm';
import {EventSubscriptionHelper} from '../components/EventSubscriptionHelper';
import {useUpdateWebhook} from '../hooks/api/use-update-webhook';
import {useWebhook} from '../hooks/api/use-webhook';
import {Webhook} from '../model/Webhook';
import {Breadcrumb} from 'akeneo-design-system';

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
    const fetchedWebhook = useWebhook(connectionCode);

    const [webhook, setWebhook] = useState(fetchedWebhook.webhook);
    useEffect(() => {
        if (!webhook) {
            setWebhook(fetchedWebhook.webhook);
        }
    }, [fetchedWebhook]);

    // Reset form on webhook change.
    useEffect(() => {
        if (webhook) {
            formMethods.reset(webhook);
        }
    }, [webhook]);

    if (fetchedWebhook.loading || !webhook) {
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
                        null === webhook.connectionImage
                            ? defaultImageUrl
                            : generateMediaUrl(webhook.connectionImage, 'thumbnail')
                    }
                    buttons={[<SaveButton key={0} webhook={webhook} setWebhook={webhook => setWebhook(webhook)} />]}
                    state={<FormState />}
                >
                    {connectionCode}
                </PageHeader>

                <PageContent>
                    <Layout>
                        <Section title={<Translate id='akeneo_connectivity.connection.webhook.event_subscription' />} />
                        <EventSubscriptionHelper />
                        <EditForm webhook={webhook} />
                    </Layout>
                </PageContent>
            </form>
        </FormContext>
    );
};

type SaveButtonProps = {
    webhook: Webhook;
    setWebhook: (webhook: Webhook) => void;
};

const SaveButton: FC<SaveButtonProps> = ({webhook, setWebhook}) => {
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

            setWebhook({...result.value, connectionImage: webhook.connectionImage});
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
