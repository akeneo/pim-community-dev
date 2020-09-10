import React, {FC, useContext} from 'react';
import {TranslateContext} from '../../shared/translate';
import {Webhook} from '../model/Webhook';
import {FormGroup, FormInput, ToggleButton} from '../../common/components';
import {CopiableCredential} from '../../settings/components/credentials/CopiableCredential';
import {RegenerateButton} from '../../settings/components/RegenerateButton';
import {useHistory} from 'react-router';
import styled from 'styled-components';
import {useForm} from "react-hook-form";

type Props = {
    webhook: Webhook;
};

export const EditForm: FC<Props> = ({webhook}) => {
    const translate = useContext(TranslateContext);
    const history = useHistory();
    const {register, errors} = useForm();

    return (
        <>
            <FormGroup label='akeneo_connectivity.connection.webhook.form.enabled' >
                <ToggleButton isChecked={webhook.enabled} ref={register} name={'enabled'}/>
            </FormGroup>
            <FormGroup
                controlId='url'
                label='akeneo_connectivity.connection.webhook.form.url'
                errors={errors.url ? [errors.url] : undefined}
            >
                <FormInput
                    type='text'
                    name='url'
                    defaultValue={webhook.url ? webhook.url : undefined}
                    ref={register}
                />
            </FormGroup>
            {webhook.enabled &&
                <CredentialList>
                    <CopiableCredential
                        label={translate('akeneo_connectivity.connection.connection.secret')}
                        actions={
                            <RegenerateButton
                                onClick={() => history.push(
                                    `/connections/${webhook.connectionCode}/webhook/regenerate-secret`
                                )}
                            />
                        }
                    >
                        {webhook.secret ? webhook.secret : ''}
                    </CopiableCredential>
                </CredentialList>
            }
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
