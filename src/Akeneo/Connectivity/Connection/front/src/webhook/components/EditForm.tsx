import React, {FC, useContext} from 'react';
import {useFormContext} from 'react-hook-form';
import {useHistory} from 'react-router';
import styled from 'styled-components';
import {FormGroup, FormInput, ToggleButton} from '../../common/components';
import {CopiableCredential} from '../../settings/components/credentials/CopiableCredential';
import {RegenerateButton} from '../../settings/components/RegenerateButton';
import {TranslateContext} from '../../shared/translate';
import {Webhook} from '../model/Webhook';

type Props = {
    webhook: Webhook;
};

export const EditForm: FC<Props> = ({webhook}: Props) => {
    const translate = useContext(TranslateContext);
    const history = useHistory();
    const {register, errors, getValues} = useFormContext();

    return (
        <>
            <FormGroup label='akeneo_connectivity.connection.webhook.form.enabled'>
                <ToggleButton name='enabled' defaultChecked={getValues('enabled')} ref={register} />
            </FormGroup>

            <FormGroup
                controlId='url'
                label='akeneo_connectivity.connection.webhook.form.url'
                errors={errors.url ? [errors.url.message] : undefined}
            >
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
export const CredentialList = styled.div`
    display: grid;
    grid-template: 'a a a' auto;
    div {
        border-top: 1px solid ${({theme}) => theme.color.grey80};
    }
`;
