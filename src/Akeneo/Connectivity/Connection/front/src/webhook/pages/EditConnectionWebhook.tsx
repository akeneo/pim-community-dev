import {useHistory, useParams} from 'react-router';
import {Loading} from '../../common/components/Loading';
import {
    ApplyButton,
    Breadcrumb,
    BreadcrumbItem,
    HelperLink,
    PageContent,
    PageHeader,
    Section,
    SmallHelper
} from '../../common/components';
import {BreadcrumbRouterLink} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {PimView} from '../../infrastructure/pim-view/PimView';
import React, {useEffect} from 'react';
import {useWebhook} from '../hooks/api/use-webhook';
import defaultImageUrl from '../../common/assets/illustrations/NewAPI.svg';
import {useMediaUrlGenerator} from '../../settings/use-media-url-generator';
import {EditForm} from '../components/EditForm';
import styled from 'styled-components';
import {useForm} from 'react-hook-form';

export const EditConnectionWebhook = () => {
    const history = useHistory();
    const {connectionCode} = useParams<{ connectionCode: string }>();
    const generateMediaUrl = useMediaUrlGenerator();
    const {loading, webhook} = useWebhook(connectionCode);

    useEffect(() => {
        if (!loading && !webhook) {
            history.push('/connections');
        }
    }, [loading, webhook]);

    if (loading || !webhook) {
        return <Loading/>;
    }

    const breadcrumb = (
        <Breadcrumb>
            <BreadcrumbRouterLink route={'oro_config_configuration_system'}>
                <Translate id='pim_menu.tab.system'/>
            </BreadcrumbRouterLink>
            <BreadcrumbItem onClick={() => history.push('/connections')}>
                <Translate id='pim_menu.item.connection_settings'/>
            </BreadcrumbItem>
            <BreadcrumbItem>
                <Translate id='akeneo_connectivity.connection.webhook.title'/>
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
        <form>
            <PageHeader
                breadcrumb={breadcrumb}
                userButtons={userButtons}
                imageSrc={
                    null === webhook.connectionImage ? defaultImageUrl : generateMediaUrl(webhook.connectionImage, 'thumbnail')
                }
                buttons={[<SaveButton key={0} />]}
                state={<FormState />}
            >
                {webhook.connectionCode}
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
                    <EditForm webhook={webhook}/>
                </Layout>
            </PageContent>
        </form>
    );
};

const SaveButton = () => {
    const {formState, handleSubmit} = useForm();
    const onSubmit = (values: any) => console.log(values);

    return (
        <ApplyButton
            key={0}
            onClick={handleSubmit(onSubmit)}
            disabled={!formState.dirty || !formState.isValid || formState.isSubmitting}
            classNames={['AknButtonList-item']}
        >
            <Translate id='pim_common.save' />
        </ApplyButton>
    );
};

const FormState = () => {
    const {formState} = useForm();

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
