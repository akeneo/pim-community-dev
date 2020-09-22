import React, {FC, useContext, useState} from 'react';
import {useForm} from 'react-hook-form';
import {useHistory} from 'react-router';
import styled from 'styled-components';
import {FormGroup, FormInput, GreyLightButton, ToggleButton} from '../../common/components';
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

export const EditForm: FC<Props> = ({webhook}) => {
    const translate = useContext(TranslateContext);
    const history = useHistory();
    const {register, getValues} = useForm();
    const checkReachability = useCheckReachability(webhook.connectionCode);
    const [resultTestUrl, setResultTestUrl] = useState<WebhookReachability>();

    const handleTestUrl = async () => {
        const result = await checkReachability(getValues('url'));

        if (isOk(result)) {
            setResultTestUrl(result.value);
        }
    };

    return (
        <>
            <FormGroup label='akeneo_connectivity.connection.webhook.form.enabled'>
                <ToggleButton isChecked={webhook.enabled} ref={register} name={'enabled'} />
            </FormGroup>

            <FormGroup
                controlId='url'
                label='akeneo_connectivity.connection.webhook.form.url'
                errors={resultTestUrl && !resultTestUrl?.success ? [resultTestUrl.message] : undefined}
                success={resultTestUrl && resultTestUrl?.success ? resultTestUrl.message : undefined}
            >
                <>
                    <FormInput
                        type='url'
                        name='url'
                        defaultValue={webhook.url ? webhook.url : undefined}
                        ref={register}
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
                                history.push(`/connections/${webhook.connectionCode}/webhook/regenerate-secret`)
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
        <GreyLightButtonContainer>
            <GreyLightButton onClick={onClick} classNames={['AknButton--apply']}>
                <Translate id='akeneo_connectivity.connection.webhook.form.test' />
            </GreyLightButton>
        </GreyLightButtonContainer>
    );
};

export const CredentialList = styled.div`
    display: grid;
    grid-template: 'a a a' auto;
    div {
        border-top: 1px solid ${({theme}) => theme.color.grey80};
    }
`;

const GreyLightButtonContainer = styled.span`
    margin-left: 10px;
`;
