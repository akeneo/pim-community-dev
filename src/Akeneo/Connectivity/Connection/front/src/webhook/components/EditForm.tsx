import React, {FC, useContext, useState} from 'react';
import {useFormContext} from 'react-hook-form';
import {useHistory} from 'react-router';
import styled from 'styled-components';
import {FormGroup, FormInput, GhostButton, ToggleButton} from '../../common/components';
import {CopiableCredential} from '../../settings/components/credentials/CopiableCredential';
import {RegenerateButton} from '../../settings/components/RegenerateButton';
import {isOk} from '../../shared/fetch-result/result';
import {Translate, TranslateContext} from '../../shared/translate';
import {useCheckReachability} from '../hooks/api/use-webhook-check-reachability';
import {Webhook} from '../model/Webhook';
import {WebhookReachability} from '../model/WebhookReachability';

type Props = {
    webhook: Webhook;
};

export const EditForm: FC<Props> = ({webhook}: Props) => {
    const translate = useContext(TranslateContext);
    const history = useHistory();
    const checkReachability = useCheckReachability(webhook.connectionCode);
    const [resultTestUrl, setResultTestUrl] = useState<WebhookReachability>();
    const {register, getValues} = useFormContext();

    const handleTestUrl = async () => {
        const result = await checkReachability(getValues('url'));

        if (isOk(result)) {
            setResultTestUrl(result.value);
        }
    };

    return (
        <>
            <FormGroup label='akeneo_connectivity.connection.webhook.form.enabled'>
                <ToggleButton name='enabled' defaultChecked={getValues('enabled')} ref={register} />
            </FormGroup>

            <FormGroup
                controlId='url'
                label='akeneo_connectivity.connection.webhook.form.url'
                errors={resultTestUrl && !resultTestUrl?.success ? [resultTestUrl.message] : undefined}
                success={resultTestUrl && resultTestUrl?.success ? resultTestUrl.message : undefined}
            >
                <>
                    <FormInput
                        type='text'
                        name='url'
                        defaultValue={getValues('url')}
                        ref={register({
                            required: {
                                value: getValues('enabled'),
                                message: 'akeneo_connectivity.connection.webhook.error.required',
                            },
                        })}
                    />
                    <TestUrlButton onClick={handleTestUrl} />
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
                    {webhook.secret ? webhook.secret : ''}
                </CopiableCredential>
            </CredentialList>
        </>
    );
};

const TestUrlButton: FC<{onClick: () => void}> = ({onClick}) => {
    return (
        <GhostButtonContainer>
            <GhostButton onClick={onClick}>
                <Translate id='akeneo_connectivity.connection.webhook.form.test' />
            </GhostButton>
        </GhostButtonContainer>
    );
};

export const CredentialList = styled.div`
    display: grid;
    grid-template: 'a a a' auto;
    div {
        border-top: 1px solid ${({theme}) => theme.color.grey80};
    }
`;

const GhostButtonContainer = styled.span`
    margin-left: 10px;
`;
