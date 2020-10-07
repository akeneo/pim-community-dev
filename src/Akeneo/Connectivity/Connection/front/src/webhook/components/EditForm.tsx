import React, { FC, useContext, useState } from 'react';
import { useFormContext } from 'react-hook-form';
import { useHistory } from 'react-router';
import styled from 'styled-components';
import { FormGroup, FormInput, ToggleButton } from '../../common/components';
import { CopiableCredential } from '../../settings/components/credentials/CopiableCredential';
import { RegenerateButton } from '../../settings/components/RegenerateButton';
import { isErr } from '../../shared/fetch-result/result';
import { TranslateContext } from '../../shared/translate';
import { useCheckReachability } from '../hooks/api/use-webhook-check-reachability';
import { Webhook } from '../model/Webhook';
import { WebhookReachability } from '../model/WebhookReachability';
import { TestUrlButton } from './TestUrlButton';

type Props = {
    webhook: Webhook;
};

export const EditForm: FC<Props> = ({webhook}: Props) => {
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

        const result = await checkReachability(getValues('url'));
        if (isErr(result)) {
            throw new Error();
        }

        setTestUrl({checking: false, status: result.value});

        if (false === result.value.success) {
            setError('url', 'manual', result.value.message);
        }
    };

    return (
        <>
            <FormGroup label='akeneo_connectivity.connection.webhook.form.enabled'>
                <ToggleButton name='enabled' ref={register} defaultChecked={webhook.enabled} />
            </FormGroup>

            <FormGroup
                controlId='url'
                label='akeneo_connectivity.connection.webhook.form.url'
                errors={errors?.url?.message && [errors?.url?.message]}
                success={true === testUrl?.status?.success ? testUrl.status.message : undefined}
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
                        onKeyDown={(event) => 'enter' === event.key && handleTestUrl()}
                    />
                    <TestUrlButton
                        onClick={handleTestUrl}
                        disabled={!getValues('url') || '' === getValues('url')}
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
