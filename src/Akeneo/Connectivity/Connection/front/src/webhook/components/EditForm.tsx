import React, {FC, useContext, useState} from 'react';
import {useForm} from 'react-hook-form';
import {useHistory} from 'react-router';
import styled from 'styled-components';
import {FormGroup, FormInput, GreyLightButton, ToggleButton} from '../../common/components';
import {CopiableCredential} from '../../settings/components/credentials/CopiableCredential';
import {RegenerateButton} from '../../settings/components/RegenerateButton';
import {isOk} from '../../shared/fetch-result/result';
import {Translate, TranslateContext, useTranslate} from '../../shared/translate';
import {useCheckAccessibility} from '../hooks/api/use-webhook-check-accessibility';
import {Webhook} from '../model/Webhook';
import {WebhookAccessibility} from '../model/WebhookAccessibility';

type Props = {
    webhook: Webhook;
};

export const EditForm: FC<Props> = ({webhook}) => {
    const translate = useContext(TranslateContext);
    const history = useHistory();
    const {register, errors, getValues} = useForm();
    const checkAccessibility = useCheckAccessibility(webhook);

    const [resultTestUrlMessage, setResultTestUrlMessage] = useState<string | null>();
    const [resultTestUrlCode, setResultTestUrlCode] = useState<number | null>();
    const [resultTestUrlSuccess, setResultTestUrlSuccess] = useState<string | null>();

    const handleClickTestUrlButton = async () => {
        const result = await checkAccessibility(getValues('url'));

        if (isOk(result)) {
            setResultTestUrlMessage(result.value.message);
            setResultTestUrlCode(result.value.code);
            setResultTestUrlSuccess(result.value.success);
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
                errors={errors.url ? [errors.url] : undefined}
            >
                <>
                    <FormInput
                        type='url'
                        name='url'
                        defaultValue={webhook.url ? webhook.url : undefined}
                        ref={register}
                    />
                    <TestUrlButton onClick={handleClickTestUrlButton} />
                </>
            </FormGroup>
            {resultTestUrlMessage && (
                <ResultTestUrl success={resultTestUrlSuccess} code={resultTestUrlCode} message={resultTestUrlMessage} />
            )}
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
export const CredentialList = styled.div`
    display: grid;
    grid-template: 'a a a' auto;
    div {
        border-top: 1px solid ${({theme}) => theme.color.grey80};
    }
`;

const TestUrlButton: FC<{onClick: () => void}> = ({onClick}) => {
    return (
        <GreyLightButtonContainer>
            <GreyLightButton onClick={onClick} classNames={['AknButton--apply']}>
                <Translate id='akeneo_connectivity.connection.webhook.form.test' />
            </GreyLightButton>
        </GreyLightButtonContainer>
    );
};

const ResultTestUrl: FC<WebhookAccessibility> = ({success, code, message}) => {
    const translate = useTranslate();
    const translatedMessage = message ? translate(message) : message;
    const completedMessage = code ? code + ': ' + translatedMessage : translatedMessage;
    return (
        <div>
            {'true' === success ? (
                <OkStatus className='AknFieldContainer-validationError'>{completedMessage}</OkStatus>
            ) : (
                <KoStatus className='AknFieldContainer-validationError'>{completedMessage}</KoStatus>
            )}
        </div>
    );
};

const OkStatus = styled.span`
    display: flex;
    align-items: baseline;
    margin-top: -14px;
    margin-bottom: 18px;
    color: #67b373;
    background: url('/bundles/pimui/images/icon-check-green.svg') no-repeat left center;
    padding-left: 26px;
    background-size: 20px;
    background-position: top left;
`;

const KoStatus = styled.span`
    display: flex;
    align-items: baseline;
    margin-top: -14px;
    margin-bottom: 18px;
    background: url('/bundles/pimui/images/icon-danger.svg') no-repeat left center;
    color: #d4604f;
    padding-left: 26px;
    background-size: 20px;
    background-position: top left;
`;

const GreyLightButtonContainer = styled.span`
    margin-left: 10px;
`;
