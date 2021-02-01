import {Helper, Link} from 'akeneo-design-system';
import React, {FC, useContext, useState} from 'react';
import {useFormContext} from 'react-hook-form';
import {useHistory} from 'react-router';
import styled from 'styled-components';
import {FormGroup, FormInput, ToggleButton} from '../../common/components';
import {CopiableCredential} from '../../settings/components/credentials/CopiableCredential';
import {RegenerateButton} from '../../settings/components/RegenerateButton';
import {isErr} from '../../shared/fetch-result/result';
import {Translate, TranslateContext} from '../../shared/translate';
import {useCheckReachability} from '../hooks/api/use-webhook-check-reachability';
import {Webhook} from '../model/Webhook';
import {WebhookReachability} from '../model/WebhookReachability';
import {TestUrlButton} from './TestUrlButton';

type Props = {
    webhook: Webhook;
    activeEventSubscriptionsLimit: {
        limit: number;
        current: number;
    };
};

export const EditForm: FC<Props> = ({webhook, activeEventSubscriptionsLimit}: Props) => {
    const translate = useContext(TranslateContext);
    const history = useHistory();

    const {register, getValues, errors, setError, clearError} = useFormContext();

    const checkReachability = useCheckReachability(webhook.connectionCode);
    const [testUrl, setTestUrl] = useState<{checking: boolean; status?: WebhookReachability}>({
        checking: false,
    });

    const handleTestUrl = async () => {
        clearError('url');
        setTestUrl({checking: true});

        const result = await checkReachability(getValues('url'), getValues('secret'));
        if (isErr(result)) {
            throw new Error();
        }

        setTestUrl({checking: false, status: result.value});

        if (false === result.value.success) {
            setError('url', 'manual', result.value.message);
        }
    };

    const isTestButtonDisabled = () =>
        !getValues('url') || '' === getValues('url') || !getValues('secret') || '' === getValues('secret');

    const isActiveEventSubscriptionsLimitReached = () =>
        activeEventSubscriptionsLimit.current >= activeEventSubscriptionsLimit.limit;

    return (
        <>
            <FormGroup
                label='akeneo_connectivity.connection.webhook.form.enabled'
                helpers={[
                    isActiveEventSubscriptionsLimitReached() && (
                        <Helper inline level='warning'>
                            <Translate
                                id='akeneo_connectivity.connection.webhook.active_event_subscriptions_limit_reached.message'
                                placeholders={{limit: activeEventSubscriptionsLimit.limit.toString()}}
                            />{' '}
                            <Link
                                href='https://help.akeneo.com/pim/serenity/articles/manage-event-subscription.html#activation'
                                target='_blank'
                            >
                                <Translate id='akeneo_connectivity.connection.webhook.active_event_subscriptions_limit_reached.link' />
                            </Link>
                        </Helper>
                    ),
                ]}
            >
                <ToggleButton
                    name='enabled'
                    ref={register}
                    defaultChecked={webhook.enabled}
                    disabled={false === webhook.enabled && isActiveEventSubscriptionsLimitReached()}
                />
            </FormGroup>

            <FormGroup
                controlId='url'
                label='akeneo_connectivity.connection.webhook.form.url'
                helpers={[
                    errors?.url?.message && (
                        <Helper inline level='error'>
                            <Translate id={errors.url.message} />
                        </Helper>
                    ),
                    true === testUrl?.status?.success && (
                        <Helper inline level='success'>
                            <Translate id={testUrl.status.message} />
                        </Helper>
                    ),
                    isTestButtonDisabled() && (
                        <Helper inline level='info'>
                            <Translate id={'akeneo_connectivity.connection.webhook.helper.url.test_disabled'} />
                        </Helper>
                    ),
                ]}
            >
                <>
                    <FormInput
                        type='text'
                        name='url'
                        ref={register({
                            required: {
                                value: getValues('enabled'),
                                message: 'akeneo_connectivity.connection.webhook.error.required',
                            },
                        })}
                        onKeyDown={event => 'enter' === event.key && handleTestUrl()}
                    />
                    <TestUrlButton
                        onClick={handleTestUrl}
                        disabled={isTestButtonDisabled()}
                        loading={testUrl.checking}
                    />
                </>
            </FormGroup>
            <CredentialList>
                <CopiableCredential
                    label={translate('akeneo_connectivity.connection.connection.secret')}
                    actions={
                        <RegenerateButton
                            onClick={() =>
                                history.push(
                                    `/connections/${webhook.connectionCode}/event-subscription/regenerate-secret`
                                )
                            }
                        />
                    }
                >
                    {webhook.secret || ''}
                </CopiableCredential>
            </CredentialList>
        </>
    );
};

export const CredentialList = styled.div`
    display: grid;
    grid-template: 'a a a' auto;
    div {
        border-top: 1px solid ${({theme}) => theme.color.grey80};
    }
`;
