import React, {useContext} from 'react';
import {useHistory, useParams} from 'react-router';
import {GreyButton, ImportantButton, Modal} from '../../common';
import styled from '../../common/styled-with-theme';
import {fetchResult} from '../../shared/fetch-result';
import {isErr} from '../../shared/fetch-result/result';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useRoute} from '../../shared/router';
import {Translate, TranslateContext} from '../../shared/translate';

export const RegenerateWebhookSecret = () => {
    const history = useHistory();
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    const {connectionCode: code} = useParams<{connectionCode: string}>();
    const url = useRoute('akeneo_connectivity_connection_webhook_rest_regenerate_secret', {code});

    const handleRedirect = () => {
        history.push(`/connections/${code}/event-subscription`);
    };

    const handleClick = async () => {
        const result = await fetchResult<undefined, undefined>(url, {
            method: 'GET',
        });

        if (isErr(result)) {
            notify({
                level: NotificationLevel.ERROR,
                title: translate('akeneo_connectivity.connection.webhook.regenerate_secret.flash.error')
            });
        } else {
            notify({
                level: NotificationLevel.SUCCESS,
                title: translate('akeneo_connectivity.connection.webhook.regenerate_secret.flash.success')
            });
        }

        handleRedirect();
    };

    const description = (
        <>
            <Translate id='akeneo_connectivity.connection.webhook.regenerate_secret.description' />
            &nbsp;
            <Link
                href='https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#revokeregenerate-your-event-subscription-secret '
                target='_blank'
            >
                <Translate id='akeneo_connectivity.connection.webhook.regenerate_secret.link' />
            </Link>
        </>
    );

    return (
        <Modal
            subTitle={<Translate id='akeneo_connectivity.connection.connections' />}
            title={<Translate id='akeneo_connectivity.connection.webhook.regenerate_secret.title' />}
            description={description}
            onCancel={handleRedirect}
        >
            <div className='AknButtonList'>
                <GreyButton onClick={handleRedirect} classNames={['AknButtonList-item']}>
                    <Translate id='pim_common.cancel' />
                </GreyButton>
                <ImportantButton onClick={handleClick} classNames={['AknButtonList-item']}>
                    <Translate id='akeneo_connectivity.connection.webhook.regenerate_secret.action.regenerate' />
                </ImportantButton>
            </div>
        </Modal>
    );
};

const Link = styled.a`
    color: ${({theme}) => theme.color.blue100};
    text-decoration: underline;
`;
