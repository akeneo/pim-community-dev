import React, {Dispatch, SetStateAction, useEffect, useState} from 'react';
import {FormContext, useForm, useFormContext} from 'react-hook-form';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import defaultImageUrl from '../../common/assets/illustrations/NewAPI.svg';
import {
    ApplyButton,
    Breadcrumb,
    BreadcrumbItem,
    HelperLink,
    PageContent,
    PageHeader,
    Section,
    SmallHelper,
} from '../../common/components';
import {Loading} from '../../common/components/Loading';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {useMediaUrlGenerator} from '../../settings/use-media-url-generator';
import {BreadcrumbRouterLink} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {EditForm} from '../components/EditForm';
import {useUpdateWebhook} from '../hooks/api/use-update-webhook';
import {useWebhook} from '../hooks/api/use-webhook';
import {isErr} from '../../shared/fetch-result/result';
import {Webhook} from '../model/Webhook';

export type FormInput = {
    connectionCode: string;
    url: string | null;
    enabled: boolean;
};

export const EditConnectionWebhook = () => {
    const history = useHistory();
    const {connectionCode} = useParams<{connectionCode: string}>();
    const generateMediaUrl = useMediaUrlGenerator();
    const {loading, webhook: fetchedWebhook} = useWebhook(connectionCode);
    const formMethods = useForm<FormInput>();
    const [webhook, setWebhook] = useState<Webhook>(
        fetchedWebhook
            ? fetchedWebhook
            : {
                  connectionCode: connectionCode,
                  enabled: false,
                  connectionImage: null,
                  secret: null,
                  url: null,
              }
    );

    useEffect(() => {
        if (!loading && !fetchedWebhook) {
            history.push('/connections');
        }
        if (!loading && fetchedWebhook) {
            setWebhook(fetchedWebhook);
        }
    }, [loading, fetchedWebhook]);

    useEffect(() => {
        formMethods.reset({
            connectionCode: webhook?.connectionCode,
            enabled: webhook?.enabled,
            url: webhook?.url,
        });
    }, [webhook]);

    if (loading || !fetchedWebhook) {
        return <Loading />;
    }

    const breadcrumb = (
        <Breadcrumb>
            <BreadcrumbRouterLink route={'oro_config_configuration_system'}>
                <Translate id='pim_menu.tab.system' />
            </BreadcrumbRouterLink>
            <BreadcrumbItem onClick={() => history.push('/connections')}>
                <Translate id='pim_menu.item.connection_settings' />
            </BreadcrumbItem>
            <BreadcrumbItem>
                <Translate id='akeneo_connectivity.connection.webhook.title' />
            </BreadcrumbItem>
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
                    buttons={[<SaveButton key={0} code={connectionCode} webhook={webhook} setWebhook={setWebhook} />]}
                    state={<FormState />}
                >
                    {connectionCode}
                </PageHeader>

                <PageContent>
                    <Layout>
                        <Section title={<Translate id='akeneo_connectivity.connection.webhook.event_subscription' />} />
                        <div>
                            <SmallHelper>
                                <Translate id='akeneo_connectivity.connection.webhook.helper.message' />
                                &nbsp;
                                <HelperLink
                                    href='https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#subscribe-to-events'
                                    target='_blank'
                                    rel='noopener noreferrer'
                                >
                                    <Translate id='akeneo_connectivity.connection.webhook.helper.link' />
                                </HelperLink>
                            </SmallHelper>
                        </div>
                        <EditForm webhook={webhook} />
                    </Layout>
                </PageContent>
            </form>
        </FormContext>
    );
};

type SaveProps = {
    code: string;
    webhook: Webhook;
    setWebhook: Dispatch<SetStateAction<Webhook>>;
};
const SaveButton = ({code, webhook, setWebhook}: SaveProps) => {
    const {formState, getValues, triggerValidation, handleSubmit, setError} = useFormContext<FormInput>();
    const updateWebhook = useUpdateWebhook(code);
    const handleSave = async () => {
        const values = getValues();
        const isValid = await triggerValidation();
        if (isValid) {
            const result = await updateWebhook({
                connectionCode: values.connectionCode,
                enabled: values.enabled,
                url: '' === values.url ? null : values.url,
            });
            if (!isErr(result)) {
                setWebhook({
                    ...webhook,
                    connectionCode: result.value.connectionCode,
                    enabled: result.value.enabled,
                    url: result.value.url,
                    secret: result.value.secret,
                });

                return;
            }
            if (result.error.errors) {
                result.error.errors.forEach(error => {
                    setError(error.field, 'validation', error.message);
                });
            }
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
